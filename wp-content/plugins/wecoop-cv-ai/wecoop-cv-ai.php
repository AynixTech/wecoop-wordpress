<?php
/**
 * Plugin Name: WeCoop CV AI
 * Plugin URI: https://www.wecoop.org
 * Description: Generazione CV con AI tramite integrazione WordPress -> BFFE.
 * Version: 1.0.0
 * Author: WeCoop Team
 * Author URI: https://www.wecoop.org
 * Text Domain: wecoop-cv-ai
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

define('WECOOP_CV_AI_VERSION', '1.0.0');
define('WECOOP_CV_AI_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WECOOP_CV_AI_PLUGIN_URL', plugin_dir_url(__FILE__));

function wecoop_cv_allowed_languages() {
    return ['it', 'es', 'en', 'fr', 'de', 'pt', 'nl', 'pl', 'ro', 'uk', 'ru', 'el', 'sv', 'cs', 'hu', 'tr'];
}

function wecoop_cv_allowed_templates() {
    return ['europass', 'modern', 'simple'];
}

function wecoop_cv_bffe_base_url() {
    if (defined('WECOOP_CV_BFFE_BASE_URL')) {
        $const_url = (string) constant('WECOOP_CV_BFFE_BASE_URL');
        if ($const_url !== '') {
            return rtrim($const_url, '/');
        }
    }

    $url = (string) get_option('wecoop_cv_bffe_base_url', '');
    return rtrim($url, '/');
}

function wecoop_cv_bffe_token() {
    if (defined('WECOOP_CV_BFFE_TOKEN')) {
        $const_token = (string) constant('WECOOP_CV_BFFE_TOKEN');
        if ($const_token !== '') {
            return $const_token;
        }
    }

    return (string) get_option('wecoop_cv_bffe_token', '');
}

function wecoop_cv_client_ip() {
    $keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];

    foreach ($keys as $key) {
        if (empty($_SERVER[$key])) {
            continue;
        }

        $raw = (string) $_SERVER[$key];
        $ip = trim(explode(',', $raw)[0]);

        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }
    }

    return '0.0.0.0';
}

function wecoop_cv_rate_limit_allow($bucket = 'generate') {
    $ip = wecoop_cv_client_ip();
    $window = 10 * MINUTE_IN_SECONDS;
    $max_requests = 30;
    $key = 'wecoop_cv_rl_' . md5($bucket . '|' . $ip);

    $state = get_transient($key);
    if (!is_array($state) || !isset($state['count'], $state['start'])) {
        $state = [
            'count' => 0,
            'start' => time(),
        ];
    }

    $elapsed = time() - (int) $state['start'];
    if ($elapsed >= $window) {
        $state = [
            'count' => 0,
            'start' => time(),
        ];
    }

    $state['count']++;
    set_transient($key, $state, $window);

    return (int) $state['count'] <= $max_requests;
}

function wecoop_cv_recursive_sanitize($value) {
    if (is_array($value)) {
        $clean = [];
        foreach ($value as $k => $v) {
            $clean[$k] = wecoop_cv_recursive_sanitize($v);
        }
        return $clean;
    }

    if (is_string($value)) {
        return sanitize_textarea_field($value);
    }

    return $value;
}

function wecoop_cv_validate_payload(array $payload) {
    $errors = [];

    $first_name = $payload['personalInfo']['firstName'] ?? '';
    $last_name = $payload['personalInfo']['lastName'] ?? '';
    $email = $payload['personalInfo']['email'] ?? '';

    if ($first_name === '') {
        $errors['personalInfo.firstName'] = 'Required';
    }
    if ($last_name === '') {
        $errors['personalInfo.lastName'] = 'Required';
    }
    if ($email === '' || !is_email($email)) {
        $errors['personalInfo.email'] = 'Invalid email format';
    }

    $experience = isset($payload['experience']) && is_array($payload['experience']) ? $payload['experience'] : [];
    $education = isset($payload['education']) && is_array($payload['education']) ? $payload['education'] : [];

    if (count($experience) < 1 && count($education) < 1) {
        $errors['experience'] = 'At least one experience or education item is required';
    }

    $language = $payload['config']['cvLanguage'] ?? '';
    if ($language !== '' && !in_array($language, wecoop_cv_allowed_languages(), true)) {
        $errors['config.cvLanguage'] = 'Language not allowed';
    }

    $template = $payload['config']['template'] ?? '';
    if ($template !== '' && !in_array($template, wecoop_cv_allowed_templates(), true)) {
        $errors['config.template'] = 'Template not allowed';
    }

    $json_size = strlen((string) wp_json_encode($payload));
    if ($json_size > 307200) {
        $errors['payload'] = 'Payload exceeds 300KB';
    }

    return $errors;
}

function wecoop_cv_error_response($status, $code, $message, $fields = []) {
    return new WP_REST_Response([
        'ok' => false,
        'error' => [
            'code' => $code,
            'message' => $message,
            'fields' => (object) $fields,
        ],
    ], $status);
}

function wecoop_cv_call_bffe($method, $path, $body = null) {
    $base_url = wecoop_cv_bffe_base_url();
    if ($base_url === '') {
        return new WP_Error('CONFIG_ERROR', 'BFFE base URL is not configured');
    }

    $url = $base_url . $path;
    $headers = [
        'Content-Type' => 'application/json',
    ];

    $token = wecoop_cv_bffe_token();
    if ($token !== '') {
        $headers['Authorization'] = 'Bearer ' . $token;
    }

    $args = [
        'method' => strtoupper($method),
        'headers' => $headers,
        'timeout' => 60,
    ];

    if ($body !== null) {
        $args['body'] = wp_json_encode($body);
    }

    $response = wp_remote_request($url, $args);
    if (is_wp_error($response)) {
        return $response;
    }

    $status = (int) wp_remote_retrieve_response_code($response);
    $raw_body = (string) wp_remote_retrieve_body($response);
    $decoded = json_decode($raw_body, true);

    if (!is_array($decoded)) {
        $decoded = [
            'ok' => false,
            'error' => [
                'code' => 'UPSTREAM_ERROR',
                'message' => 'Invalid response from CV service',
            ],
        ];
    }

    return new WP_REST_Response($decoded, $status > 0 ? $status : 502);
}

function wecoop_cv_rest_generate(WP_REST_Request $request) {
    if (!wecoop_cv_rate_limit_allow('generate')) {
        return wecoop_cv_error_response(429, 'RATE_LIMITED', 'Too many requests, retry later');
    }

    $payload = $request->get_json_params();
    if (!is_array($payload)) {
        return wecoop_cv_error_response(400, 'INVALID_BODY', 'Request body must be JSON');
    }

    $payload = wecoop_cv_recursive_sanitize($payload);
    $errors = wecoop_cv_validate_payload($payload);

    if (!empty($errors)) {
        return wecoop_cv_error_response(422, 'VALIDATION_ERROR', 'Invalid required fields', $errors);
    }

    $response = wecoop_cv_call_bffe('POST', '/api/v1/cv/generate', $payload);
    if (is_wp_error($response)) {
        return wecoop_cv_error_response(502, 'UPSTREAM_UNAVAILABLE', $response->get_error_message());
    }

    return $response;
}

function wecoop_cv_rest_get(WP_REST_Request $request) {
    $cv_id = preg_replace('/[^A-Za-z0-9_-]/', '', (string) $request['cv_id']);
    if ($cv_id === '') {
        return wecoop_cv_error_response(400, 'INVALID_CV_ID', 'cv_id is required');
    }

    $response = wecoop_cv_call_bffe('GET', '/api/v1/cv/' . rawurlencode($cv_id));
    if (is_wp_error($response)) {
        return wecoop_cv_error_response(502, 'UPSTREAM_UNAVAILABLE', $response->get_error_message());
    }

    return $response;
}

function wecoop_cv_register_rest_routes() {
    register_rest_route('wecoop/v1', '/cv/generate', [
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'wecoop_cv_rest_generate',
        'permission_callback' => '__return_true',
    ]);

    register_rest_route('wecoop/v1', '/cv/(?P<cv_id>[A-Za-z0-9_-]+)', [
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'wecoop_cv_rest_get',
        'permission_callback' => '__return_true',
    ]);
}
add_action('rest_api_init', 'wecoop_cv_register_rest_routes');

function wecoop_cv_generator_shortcode() {
    ob_start();
    ?>
    <section class="wecoop-cv-ai" data-wecoop-cv-ai>
        <h1>Generador de CV con AI</h1>
        <p>Crea un CV profesional con inteligencia artificial, personalizalo y descargalo en PDF o Word.</p>

        <form class="wecoop-cv-ai__form" novalidate>
            <div class="wecoop-cv-ai__grid">
                <label>Nombre<input type="text" name="personalInfo.firstName" required></label>
                <label>Apellido<input type="text" name="personalInfo.lastName" required></label>
                <label>Email<input type="email" name="personalInfo.email" required></label>
                <label>Telefono<input type="text" name="personalInfo.phone"></label>
                <label>Nacionalidad<input type="text" name="personalInfo.nationality"></label>
                <label>Fecha de nacimiento<input type="date" name="personalInfo.birthDate"></label>
                <label>Direccion<input type="text" name="personalInfo.address"></label>
            </div>

            <fieldset>
                <legend>Experiencia</legend>
                <div data-repeater="experience"></div>
                <button type="button" data-add-row="experience">+ Agregar experiencia</button>
            </fieldset>

            <fieldset>
                <legend>Formacion</legend>
                <div data-repeater="education"></div>
                <button type="button" data-add-row="education">+ Agregar formacion</button>
            </fieldset>

            <fieldset>
                <legend>Idiomas</legend>
                <div data-repeater="languages"></div>
                <button type="button" data-add-row="languages">+ Agregar idioma</button>
            </fieldset>

            <label>Habilidades (separate da virgola)
                <input type="text" name="skills" placeholder="Excel, SAP, Comunicacion">
            </label>

            <fieldset class="wecoop-cv-ai__grid">
                <legend>Objetivo laboral</legend>
                <label>Posicion<input type="text" name="jobGoal.position"></label>
                <label>Pais<input type="text" name="jobGoal.country"></label>
                <label>Disponibilidad<input type="text" name="jobGoal.availability"></label>
                <label>Industria<input type="text" name="jobGoal.industry"></label>
            </fieldset>

            <fieldset class="wecoop-cv-ai__grid">
                <legend>Configuracion</legend>
                <label>Modelo
                    <select name="config.template" required>
                        <option value="europass">europass</option>
                        <option value="modern">modern</option>
                        <option value="simple">simple</option>
                    </select>
                </label>
                <label>Idioma CV
                    <select name="config.cvLanguage" required>
                        <?php foreach (wecoop_cv_allowed_languages() as $lang) : ?>
                            <option value="<?php echo esc_attr($lang); ?>"><?php echo esc_html($lang); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="wecoop-cv-ai__checkbox"><input type="checkbox" name="config.includePhoto" value="1"> Incluir foto</label>
            </fieldset>

            <button type="submit" class="wecoop-cv-ai__submit">Generar CV con AI</button>
        </form>

        <p class="wecoop-cv-ai__status" data-status="idle"></p>
        <div class="wecoop-cv-ai__error" hidden></div>
        <div class="wecoop-cv-ai__result" hidden>
            <p class="wecoop-cv-ai__preview"></p>
            <a class="wecoop-cv-ai__download" data-download="pdf" href="#" target="_blank" rel="noopener">Descargar PDF</a>
            <a class="wecoop-cv-ai__download" data-download="docx" href="#" target="_blank" rel="noopener">Descargar Word</a>
        </div>
    </section>
    <?php
    return (string) ob_get_clean();
}
add_shortcode('wecoop_cv_generator', 'wecoop_cv_generator_shortcode');

function wecoop_cv_ai_seed_page() {
    $slug = 'generador-cv-ai';
    $page = get_page_by_path($slug);

    if ($page instanceof WP_Post) {
        return;
    }

    wp_insert_post([
        'post_type' => 'page',
        'post_status' => 'publish',
        'post_title' => 'Generador de CV con AI',
        'post_name' => $slug,
        'post_content' => '[wecoop_cv_generator]',
    ]);
}
add_action('admin_init', 'wecoop_cv_ai_seed_page');

function wecoop_cv_ai_filter_title($title) {
    if (is_page('generador-cv-ai')) {
        return 'Generador de CV con AI | Crea tu curriculum en minutos';
    }

    return $title;
}
add_filter('pre_get_document_title', 'wecoop_cv_ai_filter_title');

function wecoop_cv_ai_meta_description() {
    if (!is_page('generador-cv-ai')) {
        return;
    }

    echo '<meta name="description" content="Crea un CV profesional con inteligencia artificial, personalizalo y descargalo en PDF o Word." />';
}
add_action('wp_head', 'wecoop_cv_ai_meta_description');

function wecoop_cv_ai_register_settings() {
    register_setting('general', 'wecoop_cv_bffe_base_url', [
        'type' => 'string',
        'sanitize_callback' => 'esc_url_raw',
        'default' => '',
    ]);

    register_setting('general', 'wecoop_cv_bffe_token', [
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => '',
    ]);

    add_settings_field(
        'wecoop_cv_bffe_base_url',
        'WECOOP CV BFFE Base URL',
        function () {
            $value = (string) get_option('wecoop_cv_bffe_base_url', '');
            echo '<input type="url" class="regular-text" name="wecoop_cv_bffe_base_url" value="' . esc_attr($value) . '" placeholder="https://bffe.example.com" />';
        },
        'general'
    );

    add_settings_field(
        'wecoop_cv_bffe_token',
        'WECOOP CV BFFE Token',
        function () {
            $value = (string) get_option('wecoop_cv_bffe_token', '');
            echo '<input type="password" class="regular-text" name="wecoop_cv_bffe_token" value="' . esc_attr($value) . '" autocomplete="new-password" />';
        },
        'general'
    );
}
add_action('admin_init', 'wecoop_cv_ai_register_settings');

function wecoop_cv_ai_enqueue_assets() {
    $current_content = '';
    if (is_singular()) {
        $post = get_post();
        if ($post instanceof WP_Post) {
            $current_content = (string) $post->post_content;
        }
    }

    if (!is_page('generador-cv-ai') && !has_shortcode($current_content, 'wecoop_cv_generator')) {
        return;
    }

    wp_enqueue_style(
        'wecoop-cv-ai',
        WECOOP_CV_AI_PLUGIN_URL . 'assets/css/wecoop-cv-ai.css',
        [],
        WECOOP_CV_AI_VERSION
    );

    wp_enqueue_script(
        'wecoop-cv-ai',
        WECOOP_CV_AI_PLUGIN_URL . 'assets/js/wecoop-cv-ai.js',
        [],
        WECOOP_CV_AI_VERSION,
        true
    );

    wp_localize_script('wecoop-cv-ai', 'wecoopCvAiConfig', [
        'restUrl' => esc_url_raw(rest_url('wecoop/v1')),
        'nonce' => wp_create_nonce('wp_rest'),
    ]);
}
add_action('wp_enqueue_scripts', 'wecoop_cv_ai_enqueue_assets');
