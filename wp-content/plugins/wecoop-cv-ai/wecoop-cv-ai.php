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

function wecoop_cv_request_id() {
    $incoming = isset($_SERVER['HTTP_X_REQUEST_ID']) ? sanitize_text_field((string) $_SERVER['HTTP_X_REQUEST_ID']) : '';
    if ($incoming !== '') {
        return $incoming;
    }

    return 'req_' . str_replace('-', '', wp_generate_uuid4());
}

function wecoop_cv_actor_id() {
    $user_id = get_current_user_id();
    if ($user_id > 0) {
        return 'usr_' . (string) $user_id;
    }

    return 'anon';
}

function wecoop_cv_log_event($event, array $data = []) {
    $entry = array_merge([
        'event' => $event,
        'timestamp' => gmdate('c'),
    ], $data);

    error_log((string) wp_json_encode($entry));
}

function wecoop_cv_allowed_languages() {
    return ['it', 'es', 'en', 'fr', 'de', 'pt', 'nl', 'pl', 'ro', 'uk', 'ru', 'el', 'sv', 'cs', 'hu', 'tr'];
}

function wecoop_cv_allowed_templates() {
    return ['vibrant', 'formal', 'matrix', 'peach'];
}

function wecoop_cv_template_catalog() {
    $base = untrailingslashit(WECOOP_CV_AI_PLUGIN_URL) . '/template_cv/';
    $api_base = home_url('/wp-json/wecoop/v1/cv/preview');

    return [
        [
            'id' => 'vibrant',
            'name' => 'Vibrant',
            'htmlUrl' => $base . 'vibrant.html',
            'previewEndpoint' => add_query_arg(['template' => 'vibrant'], $api_base),
            'cssUrl' => $base . 'vibrant.css',
            'isDefault' => false,
        ],
        [
            'id' => 'formal',
            'name' => 'Formal',
            'htmlUrl' => $base . 'formal.html',
            'previewEndpoint' => add_query_arg(['template' => 'formal'], $api_base),
            'cssUrl' => $base . 'formal.css',
            'isDefault' => true,
        ],
        [
            'id' => 'matrix',
            'name' => 'Matrix',
            'htmlUrl' => $base . 'matrix.html',
            'previewEndpoint' => add_query_arg(['template' => 'matrix'], $api_base),
            'cssUrl' => $base . 'matrix.css',
            'isDefault' => false,
        ],
        [
            'id' => 'peach',
            'name' => 'Peach',
            'htmlUrl' => $base . 'peach.html',
            'previewEndpoint' => add_query_arg(['template' => 'peach'], $api_base),
            'cssUrl' => $base . 'peach.css',
            'isDefault' => false,
        ],
    ];
}

function wecoop_cv_resolve_template($raw_template) {
    $template = sanitize_key((string) $raw_template);

    $map = [
        'vibrant' => 'vibrant',
        'formal' => 'formal',
        'matrix' => 'matrix',
        'peach' => 'peach',
    ];

    return isset($map[$template]) ? $map[$template] : '';
}

function wecoop_cv_bffe_base_url() {
    return 'https://www.wecoop.org/api/v1/cv';
}

function wecoop_cv_bffe_token() {
    if (defined('WECOOP_CV_BFFE_TOKEN')) {
        $const_token = (string) constant('WECOOP_CV_BFFE_TOKEN');
        if ($const_token !== '') {
            return $const_token;
        }
    }

    if (defined('OPENAI_API_KEY')) {
        $openai_token = (string) constant('OPENAI_API_KEY');
        if ($openai_token !== '') {
            return $openai_token;
        }
    }

    return (string) get_option('wecoop_cv_bffe_token', '');
}

function wecoop_cv_openai_api_key() {
    if (defined('OPENAI_API_KEY')) {
        $key = (string) constant('OPENAI_API_KEY');
        if ($key !== '') {
            return $key;
        }
    }

    return '';
}

function wecoop_cv_enhance_content_with_ai(array $payload) {
    $api_key = wecoop_cv_openai_api_key();
    if ($api_key === '') {
        return [];
    }

    $lang = (string) ($payload['config']['cvLanguage'] ?? 'it');
    $lang_map = [
        'it' => 'Italian',
        'es' => 'Spanish',
        'en' => 'English',
        'fr' => 'French',
        'de' => 'German',
        'pt' => 'Portuguese',
        'nl' => 'Dutch',
        'pl' => 'Polish',
        'ro' => 'Romanian',
        'uk' => 'Ukrainian',
        'ru' => 'Russian',
        'el' => 'Greek',
        'sv' => 'Swedish',
        'cs' => 'Czech',
        'hu' => 'Hungarian',
        'tr' => 'Turkish',
    ];
    $target_language = isset($lang_map[$lang]) ? $lang_map[$lang] : 'Italian';

    $source = [
        'personalInfo' => $payload['personalInfo'] ?? [],
        'education' => $payload['education'] ?? [],
        'experience' => $payload['experience'] ?? [],
        'languages' => $payload['languages'] ?? [],
        'skills' => $payload['skills'] ?? [],
        'jobGoal' => $payload['jobGoal'] ?? [],
    ];

    $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
        'timeout' => 20,
        'headers' => [
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type' => 'application/json',
        ],
        'body' => wp_json_encode([
            'model' => 'gpt-4o-mini',
            'temperature' => 0.4,
            'response_format' => ['type' => 'json_object'],
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a professional CV writer. Return ONLY valid JSON.',
                ],
                [
                    'role' => 'user',
                    'content' => 'Using the following CV data, produce polished and realistic CV content in ' . $target_language . '. Keep style professional and concise, avoid inventing companies/roles/dates, and do not repeat the same concept. Return ONLY JSON with keys: profileSummary (string, max 80 words), keySkillsSummary (string, max 35 words), experienceHighlights (array of max 5 concise bullet strings). Data: ' . wp_json_encode($source),
                ],
            ],
        ]),
    ]);

    if (is_wp_error($response)) {
        return [];
    }

    $raw = (string) wp_remote_retrieve_body($response);
    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        return [];
    }

    $content = $decoded['choices'][0]['message']['content'] ?? '';
    if (!is_string($content) || $content === '') {
        return [];
    }

    $enhanced = json_decode($content, true);
    if (!is_array($enhanced)) {
        return [];
    }

    return $enhanced;
}

function wecoop_cv_i18n_labels($lang) {
    $map = [
        'it' => [
            'contact' => 'Contatti',
            'email' => 'Email',
            'phone' => 'Telefono',
            'goal' => 'Obiettivo',
            'profile' => 'Profilo professionale',
            'highlights' => 'Punti di forza',
            'experience' => 'Esperienza',
            'education' => 'Formazione',
            'skills' => 'Competenze',
            'languages' => 'Lingue',
            'na' => 'N/D',
        ],
        'es' => [
            'contact' => 'Contacto',
            'email' => 'Correo',
            'phone' => 'Telefono',
            'goal' => 'Objetivo',
            'profile' => 'Perfil profesional',
            'highlights' => 'Puntos fuertes',
            'experience' => 'Experiencia',
            'education' => 'Formacion',
            'skills' => 'Competencias',
            'languages' => 'Idiomas',
            'na' => 'N/D',
        ],
        'en' => [
            'contact' => 'Contact',
            'email' => 'Email',
            'phone' => 'Phone',
            'goal' => 'Objective',
            'profile' => 'Professional profile',
            'highlights' => 'Highlights',
            'experience' => 'Experience',
            'education' => 'Education',
            'skills' => 'Skills',
            'languages' => 'Languages',
            'na' => 'N/A',
        ],
    ];

    return isset($map[$lang]) ? $map[$lang] : $map['it'];
}

function wecoop_cv_format_date($value) {
    if (!is_string($value) || trim($value) === '') {
        return '';
    }

    $ts = strtotime($value);
    if ($ts === false) {
        return $value;
    }

    return gmdate('d/m/Y', $ts);
}

function wecoop_cv_upstream_path($resource = '') {
    $base_url = wecoop_cv_bffe_base_url();
    $base_path = (string) parse_url($base_url, PHP_URL_PATH);
    $resource = '/' . ltrim((string) $resource, '/');

    if (preg_match('#/api/v1/cv/?$#', $base_path) === 1) {
        return $resource === '/' ? '' : $resource;
    }

    if ($resource === '/') {
        return '/api/v1/cv';
    }

    return '/api/v1/cv' . $resource;
}

function wecoop_cv_try_load_mpdf() {
    if (class_exists('Mpdf\\Mpdf')) {
        return true;
    }

    $autoload = defined('WECOOP_SERVIZI_FILE')
        ? dirname((string) constant('WECOOP_SERVIZI_FILE')) . '/vendor/autoload.php'
        : WP_PLUGIN_DIR . '/wecoop-servizi/vendor/autoload.php';

    wecoop_cv_log_event('cv_pdf_autoload_probe', [
        'autoload' => $autoload,
        'exists' => file_exists($autoload),
    ]);

    if (file_exists($autoload)) {
        require_once $autoload;
    }

    $mpdf_exists = class_exists('Mpdf\\Mpdf');
    wecoop_cv_log_event('cv_pdf_mpdf_class_check', [
        'exists' => $mpdf_exists,
    ]);

    return $mpdf_exists;
}

function wecoop_cv_html_to_pdf($html, $filename) {
    if (!wecoop_cv_try_load_mpdf()) {
        return [
            'success' => false,
            'message' => 'Libreria mPDF non disponibile',
        ];
    }

    try {
        $has_sheet_layout = stripos((string) $html, 'class="sheet"') !== false;

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => $has_sheet_layout ? 0 : 15,
            'margin_right' => $has_sheet_layout ? 0 : 15,
            'margin_top' => $has_sheet_layout ? 0 : 15,
            'margin_bottom' => $has_sheet_layout ? 0 : 15,
            'margin_header' => 5,
            'margin_footer' => 5,
        ]);

        $mpdf->SetTitle('CV - ' . $filename);
        $mpdf->SetAuthor('WeCoop');
        $mpdf->SetDisplayMode('fullpage');
        $mpdf->WriteHTML($html);

        $upload_dir = wp_upload_dir();
        $cv_dir = $upload_dir['basedir'] . '/cv-ai';
        if (!file_exists($cv_dir)) {
            wp_mkdir_p($cv_dir);
        }

        $filepath = $cv_dir . '/' . sanitize_file_name($filename) . '.pdf';
        $url = $upload_dir['baseurl'] . '/cv-ai/' . sanitize_file_name($filename) . '.pdf';

        $mpdf->Output($filepath, 'F');

        return [
            'success' => true,
            'filepath' => $filepath,
            'url' => $url,
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Errore generazione PDF: ' . $e->getMessage(),
        ];
    }
}

function wecoop_cv_render_external_template($template, array $vars) {
    $template_dir = WECOOP_CV_AI_PLUGIN_DIR . 'template_cv/';
    $template_file = $template_dir . $template . '.html';
    $css_file = $template_dir . $template . '.css';

    if (!file_exists($template_file)) {
        return '';
    }

    $html = (string) file_get_contents($template_file);
    if ($html === '') {
        return '';
    }

    foreach ($vars as $key => $value) {
        $html = str_replace('{{' . $key . '}}', (string) $value, $html);
    }

    // mPDF renders HTML from a string and does not reliably resolve relative stylesheet links.
    // Inline the template CSS so the selected visual layout is always applied.
    if (file_exists($css_file)) {
        $css = (string) file_get_contents($css_file);
        if ($css !== '') {
            $html = preg_replace('/<link[^>]+rel=["\']stylesheet["\'][^>]*>/i', '', $html);
            $style_tag = "<style>\n" . $css . "\n</style>\n";

            if (stripos($html, '</head>') !== false) {
                $html = str_ireplace('</head>', $style_tag . '</head>', $html);
            } else {
                $html = $style_tag . $html;
            }
        }
    }

    return $html;
}

function wecoop_cv_extract_photo_url(array $payload) {
    $personal = isset($payload['personalInfo']) && is_array($payload['personalInfo']) ? $payload['personalInfo'] : [];
    $photo_mime = (string) ($personal['photoMimeType'] ?? $payload['photoMimeType'] ?? 'image/jpeg');

    $to_data_uri = static function ($raw, $mime) {
        $value = trim((string) $raw);
        if ($value === '') {
            return '';
        }

        if (stripos($value, 'data:image/') === 0) {
            return $value;
        }

        $base64 = preg_replace('/\s+/', '', $value);
        if (!is_string($base64) || $base64 === '') {
            return '';
        }

        if (!preg_match('/^[A-Za-z0-9+\/]+=*$/', $base64)) {
            return '';
        }

        if (strlen($base64) < 64) {
            return '';
        }

        $safe_mime = (strpos($mime, 'image/') === 0) ? $mime : 'image/jpeg';
        return 'data:' . $safe_mime . ';base64,' . $base64;
    };

    foreach (['photoBase64', 'photo'] as $k) {
        if (!empty($personal[$k]) && is_string($personal[$k])) {
            $data_uri = $to_data_uri((string) $personal[$k], $photo_mime);
            if ($data_uri !== '') {
                return $data_uri;
            }
        }
    }

    foreach (['photoUrl', 'photo', 'imageUrl', 'profileImage', 'avatarUrl'] as $k) {
        if (!empty($personal[$k]) && is_string($personal[$k])) {
            return trim((string) $personal[$k]);
        }
    }

    foreach (['photoBase64', 'photo'] as $k) {
        if (!empty($payload[$k]) && is_string($payload[$k])) {
            $data_uri = $to_data_uri((string) $payload[$k], $photo_mime);
            if ($data_uri !== '') {
                return $data_uri;
            }
        }
    }

    foreach (['photoUrl', 'photo', 'imageUrl', 'profileImage', 'avatarUrl'] as $k) {
        if (!empty($payload[$k]) && is_string($payload[$k])) {
            return trim((string) $payload[$k]);
        }
    }

    if (!empty($payload['documents']) && is_array($payload['documents'])) {
        foreach ($payload['documents'] as $document) {
            if (!is_array($document)) {
                continue;
            }

            $mime = strtolower((string) ($document['mimeType'] ?? $document['mime'] ?? ''));
            $kind = strtolower((string) ($document['type'] ?? $document['category'] ?? ''));
            $is_image = ($mime !== '' && strpos($mime, 'image/') === 0)
                || in_array($kind, ['image', 'photo', 'profile-photo', 'avatar'], true);

            if (!$is_image) {
                continue;
            }

            foreach (['url', 'fileUrl', 'downloadUrl', 'previewUrl'] as $url_key) {
                if (!empty($document[$url_key]) && is_string($document[$url_key])) {
                    return trim((string) $document[$url_key]);
                }
            }
        }
    }

    return '';
}

function wecoop_cv_prepare_photo_src($photo_url, $render_mode = 'pdf') {
    $src = trim((string) $photo_url);
    if ($src === '') {
        return '';
    }

    if (stripos($src, 'data:image/') === 0) {
        return $src;
    }

    if ($render_mode !== 'pdf') {
        return $src;
    }

    if (stripos($src, 'data:image/') === 0) {
        return $src;
    }

    if (!preg_match('#^https?://#i', $src)) {
        return '';
    }

    $response = wp_remote_get($src, [
        'timeout' => 12,
        'redirection' => 3,
    ]);

    if (is_wp_error($response)) {
        return $src;
    }

    $status = (int) wp_remote_retrieve_response_code($response);
    if ($status < 200 || $status >= 300) {
        return $src;
    }

    $body = (string) wp_remote_retrieve_body($response);
    $content_type = strtolower((string) wp_remote_retrieve_header($response, 'content-type'));

    if ($body === '') {
        return $src;
    }

    if (strpos($content_type, 'image/') !== 0) {
        return $src;
    }

    return 'data:' . $content_type . ';base64,' . base64_encode($body);
}

function wecoop_cv_build_local_html(array $payload, $enable_ai = true, $render_mode = 'pdf') {
    $personal = isset($payload['personalInfo']) && is_array($payload['personalInfo']) ? $payload['personalInfo'] : [];
    $full_name = trim((string) ($personal['firstName'] ?? '') . ' ' . (string) ($personal['lastName'] ?? ''));
    $email = (string) ($personal['email'] ?? '');
    $phone = (string) ($personal['phone'] ?? '');
    $address = (string) ($personal['address'] ?? '');
    $job_goal = isset($payload['jobGoal']) && is_array($payload['jobGoal']) ? $payload['jobGoal'] : [];
    $target = (string) ($job_goal['position'] ?? '');
    $lang = (string) ($payload['config']['cvLanguage'] ?? 'it');
    $template = wecoop_cv_resolve_template((string) ($payload['config']['template'] ?? 'formal'));
    if ($template === '') {
        $template = 'formal';
    }

    $include_photo = !empty($payload['config']['includePhoto']);
    $photo_url = wecoop_cv_extract_photo_url($payload);
    $photo_src = wecoop_cv_prepare_photo_src($photo_url, $render_mode);

    $labels = wecoop_cv_i18n_labels($lang);

    $experience_html = '';
    $experience_blocks = '';
    if (!empty($payload['experience']) && is_array($payload['experience'])) {
        foreach ($payload['experience'] as $exp) {
            if (!is_array($exp)) {
                continue;
            }
            $role = (string) ($exp['role'] ?? '');
            $company = (string) ($exp['company'] ?? '');
            $start = wecoop_cv_format_date((string) ($exp['startDate'] ?? ''));
            $end = wecoop_cv_format_date((string) ($exp['endDate'] ?? ''));
            $description = (string) ($exp['description'] ?? '');

            $experience_html .= '<li><strong>' . esc_html((string) ($exp['role'] ?? '')) . '</strong> - '
                . esc_html((string) ($exp['company'] ?? '')) . ' ('
                . esc_html($start) . ' - '
                . esc_html($end) . ')<br>'
                . esc_html($description) . '</li>';

            $experience_blocks .= '<div class="item"><h3>' . esc_html($role) . '</h3>'
                . '<p class="meta">' . esc_html($company) . ' | ' . esc_html($start . ' - ' . $end) . '</p>'
                . '<p>' . esc_html($description) . '</p></div>';
        }
    }

    $education_html = '';
    $education_blocks = '';
    if (!empty($payload['education']) && is_array($payload['education'])) {
        foreach ($payload['education'] as $edu) {
            if (!is_array($edu)) {
                continue;
            }
            $title = (string) ($edu['title'] ?? '');
            $institution = (string) ($edu['institution'] ?? '');
            $start = wecoop_cv_format_date((string) ($edu['startDate'] ?? ''));
            $end = wecoop_cv_format_date((string) ($edu['endDate'] ?? ''));
            $description = (string) ($edu['description'] ?? '');

            $education_html .= '<li><strong>' . esc_html((string) ($edu['title'] ?? '')) . '</strong> - '
                . esc_html((string) ($edu['institution'] ?? '')) . ' ('
                . esc_html($start) . ' - '
                . esc_html($end) . ')<br>'
                . esc_html($description) . '</li>';

            $education_blocks .= '<div class="item"><h3>' . esc_html($title) . '</h3>'
                . '<p class="meta">' . esc_html($institution) . ' | ' . esc_html($start . ' - ' . $end) . '</p>'
                . '<p>' . esc_html($description) . '</p></div>';
        }
    }

    $languages_html = '';
    if (!empty($payload['languages']) && is_array($payload['languages'])) {
        foreach ($payload['languages'] as $item) {
            if (!is_array($item)) {
                continue;
            }
            $languages_html .= '<li><strong>' . esc_html((string) ($item['language'] ?? '')) . ':</strong> '
                . esc_html((string) ($item['level'] ?? '')) . '</li>';
        }
    }

    $skills = '';
    if (!empty($payload['skills']) && is_array($payload['skills'])) {
        $skills = esc_html(implode(', ', array_map('strval', $payload['skills'])));
    }

    $enhanced = $enable_ai ? wecoop_cv_enhance_content_with_ai($payload) : [];
    $profile_summary = isset($enhanced['profileSummary']) && is_string($enhanced['profileSummary'])
        ? trim($enhanced['profileSummary'])
        : '';
    $skills_summary = isset($enhanced['keySkillsSummary']) && is_string($enhanced['keySkillsSummary'])
        ? trim($enhanced['keySkillsSummary'])
        : '';

    $highlight_html = '';
    if (!empty($enhanced['experienceHighlights']) && is_array($enhanced['experienceHighlights'])) {
        $top = array_slice($enhanced['experienceHighlights'], 0, 5);
        foreach ($top as $item) {
            if (!is_string($item) || trim($item) === '') {
                continue;
            }
            $highlight_html .= '<li>' . esc_html(trim($item)) . '</li>';
        }
    }

    $photo_html = '';
    if ($include_photo && $photo_src !== '') {
        $photo_html = '<img class="cv-photo" src="' . esc_attr($photo_src) . '" alt="photo">';
    }

    if ($render_mode === 'preview') {
        $external = wecoop_cv_render_external_template($template, [
            'full_name' => esc_html($full_name !== '' ? $full_name : 'N/A'),
            'job_title' => esc_html($target),
            'label_contact' => esc_html($labels['contact']),
            'email' => esc_html($email),
            'phone' => esc_html($phone),
            'address' => esc_html($address),
            'label_profile' => esc_html($labels['profile']),
            'label_experience' => esc_html($labels['experience']),
            'label_education' => esc_html($labels['education']),
            'label_skills' => esc_html($labels['skills']),
            'label_languages' => esc_html($labels['languages']),
            'profile_summary' => esc_html($profile_summary !== '' ? $profile_summary : $labels['na']),
            'skills_list' => ($skills !== '' ? '<p>' . $skills . '</p>' : '<p>' . esc_html($labels['na']) . '</p>'),
            'languages_list' => ($languages_html !== '' ? '<ul>' . $languages_html . '</ul>' : '<p>' . esc_html($labels['na']) . '</p>'),
            'experience_list' => ($experience_blocks !== '' ? $experience_blocks : '<p>' . esc_html($labels['na']) . '</p>'),
            'education_list' => ($education_blocks !== '' ? $education_blocks : '<p>' . esc_html($labels['na']) . '</p>'),
            'photo_url' => esc_attr($photo_src),
        ]);

        if ($external !== '') {
            return $external;
        }
    }

    if ($template === 'matrix') {
        return '<!doctype html><html><head><meta charset="UTF-8"><style>'
            . 'body{font-family:Arial,sans-serif;font-size:11px;color:#111;margin:0;}h1{font-size:32px;margin:0 0 4px;}h2{font-size:18px;margin:18px 0 10px;text-transform:uppercase;letter-spacing:.5px;}h3{font-size:14px;margin:0 0 4px;}'
            . '.layout{width:100%;border-collapse:collapse;} .left{width:33%;background:#1f2a3d;color:#fff;padding:24px;vertical-align:top;} .right{width:67%;padding:24px;vertical-align:top;background:#fff;} .muted{opacity:.9;} .line{border-top:1px solid #bcc4cf;margin:6px 0 14px;} ul{padding-left:18px;margin:0;} li{margin:0 0 6px;} .cv-photo{width:120px;height:120px;border-radius:50%;object-fit:cover;border:3px solid #fff;margin-bottom:14px;} .item{margin-bottom:12px;} .meta{color:#555;margin:0 0 5px;}'
            . '</style></head><body>'
            . '<table class="layout"><tr><td class="left">'
            . $photo_html
            . '<h1 style="font-size:28px;color:#fff;">' . esc_html($full_name !== '' ? $full_name : 'N/A') . '</h1>'
            . '<p class="muted">' . esc_html($target) . '</p>'
            . '<div class="line"></div>'
            . '<h2 style="color:#fff;">' . esc_html($labels['email']) . ' / ' . esc_html($labels['phone']) . '</h2>'
            . '<p>' . esc_html($email) . '<br>' . esc_html($phone) . '<br>' . esc_html($address) . '</p>'
            . '<h2 style="color:#fff;">' . esc_html($labels['skills']) . '</h2><p>' . ($skills !== '' ? $skills : esc_html($labels['na'])) . '</p>'
            . '<h2 style="color:#fff;">' . esc_html($labels['languages']) . '</h2><ul>' . ($languages_html !== '' ? $languages_html : '<li>' . esc_html($labels['na']) . '</li>') . '</ul>'
            . '</td><td class="right">'
            . ($profile_summary !== '' ? '<h2>' . esc_html($labels['profile']) . '</h2><p>' . esc_html($profile_summary) . '</p>' : '')
            . ($highlight_html !== '' ? '<h2>' . esc_html($labels['highlights']) . '</h2><ul>' . $highlight_html . '</ul>' : '')
            . '<h2>' . esc_html($labels['experience']) . '</h2>' . ($experience_blocks !== '' ? $experience_blocks : '<p>' . esc_html($labels['na']) . '</p>')
            . '<h2>' . esc_html($labels['education']) . '</h2>' . ($education_blocks !== '' ? $education_blocks : '<p>' . esc_html($labels['na']) . '</p>')
            . '</td></tr></table></body></html>';
    }

    if ($template === 'formal') {
        return '<!doctype html><html><head><meta charset="UTF-8"><style>'
            . 'body{font-family:Arial,sans-serif;font-size:11px;color:#111;} h1{font-size:30px;margin:0;} h2{font-size:17px;margin:18px 0 8px;text-transform:uppercase;border-bottom:2px solid #333;padding-bottom:4px;} h3{font-size:14px;margin:0 0 4px;} .meta{color:#444;} .item{margin-bottom:10px;} ul{padding-left:18px;} li{margin-bottom:6px;} .head{display:table;width:100%;} .head-left,.head-right{display:table-cell;vertical-align:top;} .head-right{text-align:right;} .cv-photo{width:110px;height:110px;object-fit:cover;border-radius:8px;}'
            . '</style></head><body>'
            . '<div class="head"><div class="head-left"><h1>' . esc_html($full_name !== '' ? $full_name : 'N/A') . '</h1><p class="meta">' . esc_html($labels['goal']) . ': ' . esc_html($target) . '</p><p class="meta">' . esc_html($labels['email']) . ': ' . esc_html($email) . ' | ' . esc_html($labels['phone']) . ': ' . esc_html($phone) . '</p></div><div class="head-right">' . $photo_html . '</div></div>'
            . ($profile_summary !== '' ? '<h2>' . esc_html($labels['profile']) . '</h2><p>' . esc_html($profile_summary) . '</p>' : '')
            . '<h2>' . esc_html($labels['experience']) . '</h2>' . ($experience_blocks !== '' ? $experience_blocks : '<p>' . esc_html($labels['na']) . '</p>')
            . '<h2>' . esc_html($labels['education']) . '</h2>' . ($education_blocks !== '' ? $education_blocks : '<p>' . esc_html($labels['na']) . '</p>')
            . '<h2>' . esc_html($labels['skills']) . '</h2><p>' . ($skills !== '' ? $skills : esc_html($labels['na'])) . '</p>'
            . ($skills_summary !== '' ? '<p class="meta">' . esc_html($skills_summary) . '</p>' : '')
            . '<h2>' . esc_html($labels['languages']) . '</h2><ul>' . ($languages_html !== '' ? $languages_html : '<li>' . esc_html($labels['na']) . '</li>') . '</ul>'
            . '</body></html>';
    }

    if ($template === 'vibrant') {
        return '<!doctype html><html><head><meta charset="UTF-8"><style>'
            . 'body{font-family:Arial,sans-serif;font-size:11px;color:#111;margin:0;background:#f2f2f2;}h1{margin:0;color:#f3c623;font-size:32px;}h2{font-size:16px;margin:16px 0 8px;text-transform:uppercase;border-bottom:2px solid #f3c623;padding-bottom:4px;}h3{font-size:14px;margin:0 0 4px;}'
            . '.layout{width:100%;border-collapse:collapse;} .left{width:34%;background:#1f2a3d;color:#fff;padding:22px;vertical-align:top;} .right{width:66%;background:#fff;padding:22px;vertical-align:top;} .role{margin:6px 0 10px;color:#d4d9e3;} .item{margin-bottom:12px;} .meta{color:#555;margin:0 0 5px;} ul{padding-left:18px;} li{margin-bottom:6px;} .cv-photo{width:120px;height:120px;border-radius:50%;object-fit:cover;border:4px solid #f3c623;display:block;margin:0 auto 14px;} .left p{margin:0 0 6px;} .right h2{border-bottom-color:#d4d9e3;color:#1f2a3d;}'
            . '</style></head><body>'
            . '<table class="layout"><tr><td class="left">'
            . $photo_html
            . '<h1>' . esc_html($full_name !== '' ? $full_name : 'N/A') . '</h1>'
            . '<p class="role">' . esc_html($target) . '</p>'
            . '<h2>' . esc_html($labels['contact']) . '</h2>'
            . '<p>' . esc_html($email) . '</p><p>' . esc_html($phone) . '</p><p>' . esc_html($address) . '</p>'
            . '<h2>' . esc_html($labels['skills']) . '</h2><p>' . ($skills !== '' ? $skills : esc_html($labels['na'])) . '</p>'
            . '<h2>' . esc_html($labels['languages']) . '</h2><ul>' . ($languages_html !== '' ? $languages_html : '<li>' . esc_html($labels['na']) . '</li>') . '</ul>'
            . '</td><td class="right">'
            . '<h2>' . esc_html($labels['profile']) . '</h2><p>' . esc_html($profile_summary !== '' ? $profile_summary : $labels['na']) . '</p>'
            . ($highlight_html !== '' ? '<h2>' . esc_html($labels['highlights']) . '</h2><ul>' . $highlight_html . '</ul>' : '')
            . '<h2>' . esc_html($labels['experience']) . '</h2>' . ($experience_blocks !== '' ? $experience_blocks : '<p>' . esc_html($labels['na']) . '</p>')
            . '<h2>' . esc_html($labels['education']) . '</h2>' . ($education_blocks !== '' ? $education_blocks : '<p>' . esc_html($labels['na']) . '</p>')
            . '</td></tr></table></body></html>';
    }

    if ($template === 'peach') {
        return '<!doctype html><html><head><meta charset="UTF-8"><style>'
            . 'body{font-family:Arial,sans-serif;font-size:11px;color:#222;margin:0;background:#f7f1f2;}h1{font-size:34px;margin:0;color:#f4c6cf;letter-spacing:1px;}h2{font-size:16px;margin:14px 0 8px;text-transform:uppercase;letter-spacing:2px;background:#f1c7cf;padding:6px 8px;}h3{font-size:14px;margin:0 0 4px;}'
            . '.wrap{width:100%;border-collapse:collapse;} .left{width:35%;background:#e9b8c1;padding:22px;vertical-align:top;} .right{width:65%;background:#fff;padding:22px;vertical-align:top;} .meta{color:#4a4a4a;} .cv-photo{width:140px;height:140px;object-fit:cover;border:4px solid #fff;margin-bottom:14px;} ul{padding-left:18px;} li{margin-bottom:6px;} .item{margin-bottom:12px;}'
            . '</style></head><body>'
            . '<table class="wrap"><tr><td class="left">'
            . $photo_html
            . '<h2>' . esc_html($labels['email']) . ' / ' . esc_html($labels['phone']) . '</h2>'
            . '<p class="meta">' . esc_html($email) . '<br>' . esc_html($phone) . '<br>' . esc_html($address) . '</p>'
            . '<h2>' . esc_html($labels['profile']) . '</h2><p>' . esc_html($profile_summary !== '' ? $profile_summary : $labels['na']) . '</p>'
            . '<h2>' . esc_html($labels['skills']) . '</h2><p>' . ($skills !== '' ? $skills : esc_html($labels['na'])) . '</p>'
            . '<h2>' . esc_html($labels['languages']) . '</h2><ul>' . ($languages_html !== '' ? $languages_html : '<li>' . esc_html($labels['na']) . '</li>') . '</ul>'
            . '</td><td class="right">'
            . '<h1>' . esc_html($full_name !== '' ? $full_name : 'N/A') . '</h1>'
            . '<p class="meta">' . esc_html($labels['goal']) . ': ' . esc_html($target) . '</p>'
            . '<h2>' . esc_html($labels['education']) . '</h2>' . ($education_blocks !== '' ? $education_blocks : '<p>' . esc_html($labels['na']) . '</p>')
            . '<h2>' . esc_html($labels['experience']) . '</h2>' . ($experience_blocks !== '' ? $experience_blocks : '<p>' . esc_html($labels['na']) . '</p>')
            . '</td></tr></table></body></html>';
    }

    return '<!doctype html><html><head><meta charset="UTF-8"><style>'
        . 'body{font-family:Arial,sans-serif;font-size:12px;color:#111;}h1{font-size:24px;margin-bottom:6px;}h2{font-size:16px;margin:18px 0 8px;}ul{padding-left:18px;}li{margin-bottom:8px;} .meta{color:#444;}'
        . '</style></head><body>'
        . '<h1>' . esc_html($full_name) . '</h1>'
        . '<p class="meta">' . esc_html($labels['email']) . ': ' . esc_html($email) . ' | ' . esc_html($labels['phone']) . ': ' . esc_html($phone) . '</p>'
        . '<p class="meta">' . esc_html($labels['goal']) . ': ' . esc_html($target) . '</p>'
        . ($profile_summary !== '' ? '<h2>' . esc_html($labels['profile']) . '</h2><p>' . esc_html($profile_summary) . '</p>' : '')
        . ($highlight_html !== '' ? '<h2>' . esc_html($labels['highlights']) . '</h2><ul>' . $highlight_html . '</ul>' : '')
        . '<h2>' . esc_html($labels['experience']) . '</h2><ul>' . ($experience_html !== '' ? $experience_html : '<li>' . esc_html($labels['na']) . '</li>') . '</ul>'
        . '<h2>' . esc_html($labels['education']) . '</h2><ul>' . ($education_html !== '' ? $education_html : '<li>' . esc_html($labels['na']) . '</li>') . '</ul>'
        . '<h2>' . esc_html($labels['skills']) . '</h2><p>' . ($skills !== '' ? $skills : esc_html($labels['na'])) . '</p>'
        . ($skills_summary !== '' ? '<p class="meta">' . esc_html($skills_summary) . '</p>' : '')
        . '<h2>' . esc_html($labels['languages']) . '</h2><ul>' . ($languages_html !== '' ? $languages_html : '<li>' . esc_html($labels['na']) . '</li>') . '</ul>'
        . '</body></html>';
}

function wecoop_cv_generate_local_fallback(array $payload, $request_id) {
    $cv_id = 'cv_local_' . substr(str_replace('-', '', wp_generate_uuid4()), 0, 16);
    $template = wecoop_cv_resolve_template((string) ($payload['config']['template'] ?? 'formal'));
    if ($template === '') {
        $template = 'formal';
    }
    $html = wecoop_cv_build_local_html($payload, true, 'pdf');

    $pdf = wecoop_cv_html_to_pdf($html, $cv_id);
    if (empty($pdf['success'])) {
        return new WP_Error('PDF_GENERATION_FAILED', (string) ($pdf['message'] ?? 'Errore generazione PDF'));
    }

    wecoop_cv_log_event('cv_generate_local_fallback_completed', [
        'requestId' => $request_id,
        'cvId' => $cv_id,
        'pdfGenerated' => true,
    ]);

    return [
        'ok' => true,
        'requestId' => $request_id,
        'cvId' => $cv_id,
        'status' => 'generated',
        'template' => $template,
        'cvLanguage' => (string) ($payload['config']['cvLanguage'] ?? 'it'),
        'previewText' => 'CV generato in fallback locale WordPress.',
        'files' => [
            'pdfUrl' => (string) $pdf['url'],
            'docxUrl' => null,
        ],
        'createdAt' => gmdate('c'),
    ];
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

function wecoop_cv_normalize_payload(array $payload) {
    $personal = isset($payload['personalInfo']) && is_array($payload['personalInfo']) ? $payload['personalInfo'] : [];
    $config = isset($payload['config']) && is_array($payload['config']) ? $payload['config'] : [];

    $normalized_photo = wecoop_cv_extract_photo_url($payload);
    if ($normalized_photo !== '' && empty($personal['photoUrl'])) {
        $personal['photoUrl'] = $normalized_photo;
    }

    if (empty($personal['firstName'])) {
        $personal['firstName'] = (string) ($personal['first_name'] ?? $personal['name'] ?? $personal['givenName'] ?? '');
    }

    if (empty($personal['lastName'])) {
        $personal['lastName'] = (string) ($personal['last_name'] ?? $personal['surname'] ?? $personal['familyName'] ?? '');
    }

    if ((empty($personal['firstName']) || empty($personal['lastName'])) && !empty($personal['fullName'])) {
        $parts = preg_split('/\s+/', trim((string) $personal['fullName']));
        if (is_array($parts) && !empty($parts)) {
            if (empty($personal['firstName'])) {
                $personal['firstName'] = (string) array_shift($parts);
            }
            if (empty($personal['lastName'])) {
                $personal['lastName'] = !empty($parts) ? (string) implode(' ', $parts) : '';
            }
        }
    }

    if (empty($personal['email'])) {
        $personal['email'] = (string) ($personal['emailAddress'] ?? $personal['mail'] ?? '');
    }

    if (empty($config['template'])) {
        $config['template'] = (string) ($payload['cvModel'] ?? $config['model'] ?? $config['templateId'] ?? '');
    }

    if (empty($config['cvLanguage'])) {
        $config['cvLanguage'] = (string) ($payload['cvLanguage'] ?? $config['language'] ?? $config['cvLang'] ?? '');
    }

    if (!isset($config['includePhoto'])) {
        $config['includePhoto'] = !empty($payload['hasPhoto']) || !empty($personal['hasPhoto']) || !empty($config['hasPhoto']);
    }

    $payload['personalInfo'] = $personal;
    $payload['config'] = $config;

    if (isset($payload['skills']) && is_string($payload['skills'])) {
        $skills = array_filter(array_map('trim', explode(',', $payload['skills'])));
        $payload['skills'] = array_values($skills);
    }

    foreach (['experience', 'education', 'languages', 'skills'] as $list_key) {
        if (isset($payload[$list_key]) && !is_array($payload[$list_key])) {
            $payload[$list_key] = [];
        }
    }

    return $payload;
}

function wecoop_cv_validate_payload(array $payload) {
    $errors = [];

    $first_name = trim((string) ($payload['personalInfo']['firstName'] ?? ''));
    $last_name = trim((string) ($payload['personalInfo']['lastName'] ?? ''));
    $email = $payload['personalInfo']['email'] ?? '';

    if ($first_name === '' && $last_name === '') {
        $errors['personalInfo.firstName'] = 'At least a name is required';
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
    if ($template !== '' && wecoop_cv_resolve_template((string) $template) === '') {
        $errors['config.template'] = 'Template not allowed';
    }

    $json_size = strlen((string) wp_json_encode($payload));
    if ($json_size > 307200) {
        $errors['payload'] = 'Payload exceeds 300KB';
    }

    return $errors;
}

function wecoop_cv_error_response($status, $code, $message, $fields = [], $request_id = '') {
    $payload = [
        'ok' => false,
        'requestId' => $request_id,
        'error' => [
            'code' => $code,
            'message' => $message,
            'fields' => (object) $fields,
        ],
    ];

    $response = new WP_REST_Response($payload, $status);
    if ($request_id !== '') {
        $response->header('X-Request-Id', $request_id);
    }

    return $response;
}

function wecoop_cv_call_bffe($method, $path, $body = null, array $query = [], $request_id = '') {
    $base_url = wecoop_cv_bffe_base_url();
    if ($base_url === '') {
        return new WP_Error(
            'CONFIG_ERROR',
            'BFFE base URL is not configured.'
        );
    }

    $url = $base_url . $path;
    if (!empty($query)) {
        $url = add_query_arg($query, $url);
    }

    $headers = [
        'Content-Type' => 'application/json',
    ];

    if ($request_id !== '') {
        $headers['X-Request-Id'] = $request_id;
    }

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
    $content_type = (string) wp_remote_retrieve_header($response, 'content-type');
    $decoded = json_decode($raw_body, true);

    if (!is_array($decoded)) {
        $decoded = [
            'ok' => false,
            'requestId' => $request_id,
            'error' => [
                'code' => 'UPSTREAM_ERROR',
                'message' => 'Invalid response from CV service',
                'upstream' => [
                    'url' => $url,
                    'statusCode' => $status,
                    'contentType' => $content_type,
                    'bodyPreview' => mb_substr(wp_strip_all_tags($raw_body), 0, 240),
                ],
            ],
        ];

        $error_status = ($status >= 400 && $status <= 599) ? $status : 502;
        $response_obj = new WP_REST_Response($decoded, $error_status);
        if ($request_id !== '') {
            $response_obj->header('X-Request-Id', $request_id);
        }

        return $response_obj;
    }

    $response_obj = new WP_REST_Response($decoded, $status > 0 ? $status : 502);
    if ($request_id !== '') {
        $response_obj->header('X-Request-Id', $request_id);
    }

    return $response_obj;
}

function wecoop_cv_rest_generate(WP_REST_Request $request) {
    $request_id = wecoop_cv_request_id();
    $actor_id = wecoop_cv_actor_id();
    $start_ts = microtime(true);

    if (!wecoop_cv_rate_limit_allow('generate')) {
        return wecoop_cv_error_response(429, 'RATE_LIMITED', 'Too many requests, retry later', [], $request_id);
    }

    $payload = $request->get_json_params();
    if (!is_array($payload)) {
        return wecoop_cv_error_response(400, 'INVALID_BODY', 'Request body must be JSON', [], $request_id);
    }

    $payload = wecoop_cv_recursive_sanitize($payload);
    $payload = wecoop_cv_normalize_payload($payload);
    $payload_size = strlen((string) wp_json_encode($payload));
    $summary = [
        'educationCount' => is_array($payload['education'] ?? null) ? count($payload['education']) : 0,
        'experienceCount' => is_array($payload['experience'] ?? null) ? count($payload['experience']) : 0,
        'languagesCount' => is_array($payload['languages'] ?? null) ? count($payload['languages']) : 0,
        'skillsCount' => is_array($payload['skills'] ?? null) ? count($payload['skills']) : 0,
    ];

    wecoop_cv_log_event('cv_generate_request_received', [
        'requestId' => $request_id,
        'userId' => $actor_id,
        'method' => 'POST',
        'path' => '/api/v1/cv/generate',
        'payloadSizeBytes' => $payload_size,
        'summary' => $summary,
    ]);

    $errors = wecoop_cv_validate_payload($payload);

    if (!empty($errors)) {
        wecoop_cv_log_event('cv_generate_validation_failed', [
            'requestId' => $request_id,
            'userId' => $actor_id,
            'invalidFields' => array_keys($errors),
        ]);

        return wecoop_cv_error_response(422, 'VALIDATION_ERROR', 'Invalid required fields', $errors, $request_id);
    }

    wecoop_cv_log_event('cv_generate_ai_request_started', [
        'requestId' => $request_id,
        'provider' => 'bffe',
        'template' => (string) ($payload['config']['template'] ?? ''),
        'cvLanguage' => (string) ($payload['config']['cvLanguage'] ?? ''),
    ]);

    $ai_start_ts = microtime(true);
    $response = wecoop_cv_call_bffe('POST', wecoop_cv_upstream_path('/generate'), $payload, [], $request_id);
    $ai_duration = (int) round((microtime(true) - $ai_start_ts) * 1000);

    if (is_wp_error($response)) {
        return wecoop_cv_error_response(502, 'UPSTREAM_UNAVAILABLE', $response->get_error_message(), [], $request_id);
    }

    $response_data = $response->get_data();
    $response_status = $response->get_status();

    if (
        $response_status === 404
        && is_array($response_data)
        && (($response_data['error']['code'] ?? '') === 'UPSTREAM_ERROR')
    ) {
        $fallback = wecoop_cv_generate_local_fallback($payload, $request_id);
        if (is_wp_error($fallback)) {
            return wecoop_cv_error_response(502, 'LOCAL_FALLBACK_FAILED', $fallback->get_error_message(), [], $request_id);
        }

        $fallback_response = new WP_REST_Response($fallback, 200);
        $fallback_response->header('X-Request-Id', $request_id);
        return $fallback_response;
    }

    $cv_id = is_array($response_data) ? (string) ($response_data['cvId'] ?? '') : '';

    wecoop_cv_log_event('cv_generate_ai_request_completed', [
        'requestId' => $request_id,
        'provider' => 'bffe',
        'durationMs' => $ai_duration,
        'tokenUsage' => null,
    ]);

    wecoop_cv_log_event('cv_generate_file_build_completed', [
        'requestId' => $request_id,
        'cvId' => $cv_id,
        'pdfGenerated' => !empty($response_data['files']['pdfUrl']),
        'docxGenerated' => !empty($response_data['files']['docxUrl']),
        'durationMs' => null,
    ]);

    wecoop_cv_log_event('cv_generate_response_sent', [
        'requestId' => $request_id,
        'cvId' => $cv_id,
        'statusCode' => $response->get_status(),
        'totalDurationMs' => (int) round((microtime(true) - $start_ts) * 1000),
    ]);

    return $response;
}

function wecoop_cv_rest_get(WP_REST_Request $request) {
    $request_id = wecoop_cv_request_id();
    $cv_id = preg_replace('/[^A-Za-z0-9_-]/', '', (string) $request['cv_id']);

    if ($cv_id === 'templates') {
        return wecoop_cv_rest_templates($request);
    }

    if ($cv_id === '') {
        return wecoop_cv_error_response(400, 'INVALID_CV_ID', 'cv_id is required', [], $request_id);
    }

    $response = wecoop_cv_call_bffe('GET', wecoop_cv_upstream_path('/' . rawurlencode($cv_id)), null, [], $request_id);
    if (is_wp_error($response)) {
        return wecoop_cv_error_response(502, 'UPSTREAM_UNAVAILABLE', $response->get_error_message(), [], $request_id);
    }

    return $response;
}

function wecoop_cv_rest_list(WP_REST_Request $request) {
    $request_id = wecoop_cv_request_id();

    if (!wecoop_cv_rate_limit_allow('list')) {
        return wecoop_cv_error_response(429, 'RATE_LIMITED', 'Too many requests, retry later', [], $request_id);
    }

    $page = max(1, (int) $request->get_param('page'));
    $limit = (int) $request->get_param('limit');
    if ($limit <= 0) {
        $limit = 10;
    }
    $limit = min(50, $limit);

    $status = sanitize_key((string) $request->get_param('status'));
    $language = sanitize_key((string) $request->get_param('language'));

    $allowed_status = ['generated', 'processing', 'failed'];
    $fields = [];

    if ($status !== '' && !in_array($status, $allowed_status, true)) {
        $fields['status'] = 'Status not allowed';
    }

    if ($language !== '' && !in_array($language, wecoop_cv_allowed_languages(), true)) {
        $fields['language'] = 'Language not allowed';
    }

    if (!empty($fields)) {
        return wecoop_cv_error_response(422, 'VALIDATION_ERROR', 'Invalid query parameters', $fields, $request_id);
    }

    $query = [
        'page' => $page,
        'limit' => $limit,
    ];

    if ($status !== '') {
        $query['status'] = $status;
    }

    if ($language !== '') {
        $query['language'] = $language;
    }

    $response = wecoop_cv_call_bffe('GET', wecoop_cv_upstream_path(''), null, $query, $request_id);
    if (is_wp_error($response)) {
        return wecoop_cv_error_response(502, 'UPSTREAM_UNAVAILABLE', $response->get_error_message(), [], $request_id);
    }

    return $response;
}

function wecoop_cv_rest_templates(WP_REST_Request $request) {
    $request_id = wecoop_cv_request_id();

    if (!wecoop_cv_rate_limit_allow('templates')) {
        return wecoop_cv_error_response(429, 'RATE_LIMITED', 'Too many requests, retry later', [], $request_id);
    }

    $catalog = wecoop_cv_template_catalog();
    $default = 'formal';
    $allowed = wecoop_cv_allowed_templates();

    if (isset($request['default']) && $request['default'] !== null) {
        $requested_default = wecoop_cv_resolve_template((string) $request['default']);
        if ($requested_default !== '' && in_array($requested_default, $allowed, true)) {
            $default = $requested_default;
        }
    }

    foreach ($catalog as &$item) {
        $item['isDefault'] = ($item['id'] === $default);
    }
    unset($item);

    return new WP_REST_Response([
        'ok' => true,
        'defaultTemplate' => $default,
        'items' => $catalog,
    ], 200);
}

function wecoop_cv_rest_preview(WP_REST_Request $request) {
    $request_id = wecoop_cv_request_id();

    if (!wecoop_cv_rate_limit_allow('preview')) {
        return wecoop_cv_error_response(429, 'RATE_LIMITED', 'Too many requests, retry later', [], $request_id);
    }

    $payload = $request->get_json_params();
    if (!is_array($payload)) {
        $payload = [];
    }

    $payload = wecoop_cv_recursive_sanitize($payload);
    $payload = wecoop_cv_normalize_payload($payload);
    if (!isset($payload['config']) || !is_array($payload['config'])) {
        $payload['config'] = [];
    }

    $template_from_query = wecoop_cv_resolve_template((string) $request->get_param('template'));
    $template_from_body = wecoop_cv_resolve_template((string) ($payload['config']['template'] ?? ''));
    $template = $template_from_query !== '' ? $template_from_query : $template_from_body;
    if ($template === '') {
        $template = 'formal';
    }
    $payload['config']['template'] = $template;

    $language = sanitize_key((string) ($payload['config']['cvLanguage'] ?? ''));
    if ($language !== '' && !in_array($language, wecoop_cv_allowed_languages(), true)) {
        return wecoop_cv_error_response(422, 'VALIDATION_ERROR', 'Invalid language', ['config.cvLanguage' => 'Language not allowed'], $request_id);
    }

    $html = wecoop_cv_build_local_html($payload, false, 'preview');

    return new WP_REST_Response([
        'ok' => true,
        'requestId' => $request_id,
        'template' => $template,
        'html' => $html,
    ], 200);
}

function wecoop_cv_register_rest_routes() {
    register_rest_route('wecoop/v1', '/cv/generate', [
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'wecoop_cv_rest_generate',
        'permission_callback' => '__return_true',
    ]);

    register_rest_route('wecoop/v1', '/cv/templates', [
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'wecoop_cv_rest_templates',
        'permission_callback' => '__return_true',
        'args' => [
            'default' => [
                'type' => 'string',
                'required' => false,
            ],
        ],
    ]);

    register_rest_route('wecoop/v1', '/cv/preview', [
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'wecoop_cv_rest_preview',
        'permission_callback' => '__return_true',
        'args' => [
            'template' => [
                'type' => 'string',
                'required' => false,
            ],
        ],
    ]);

    register_rest_route('wecoop/v1', '/cv/(?P<cv_id>[A-Za-z0-9_-]+)', [
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'wecoop_cv_rest_get',
        'permission_callback' => '__return_true',
    ]);

    register_rest_route('wecoop/v1', '/cv', [
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'wecoop_cv_rest_list',
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
                        <option value="vibrant">vibrant</option>
                        <option value="formal" selected>formal</option>
                        <option value="matrix">matrix</option>
                        <option value="peach">peach</option>
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

        <section class="wecoop-cv-ai__history">
            <h2>CVs generados</h2>
            <form class="wecoop-cv-ai__history-filters" data-history-form>
                <label>Estado
                    <select name="status">
                        <option value="">Todos</option>
                        <option value="generated">generated</option>
                        <option value="processing">processing</option>
                        <option value="failed">failed</option>
                    </select>
                </label>
                <label>Idioma
                    <select name="language">
                        <option value="">Todos</option>
                        <?php foreach (wecoop_cv_allowed_languages() as $lang) : ?>
                            <option value="<?php echo esc_attr($lang); ?>"><?php echo esc_html($lang); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <button type="submit">Filtrar</button>
            </form>
            <div class="wecoop-cv-ai__history-list" data-history-list></div>
        </section>
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
    register_setting('general', 'wecoop_cv_bffe_token', [
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => '',
    ]);

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
