<?php

/**
 * Custom Facebook Feed block with live preview.
 *
 * @since 2.3
 */

namespace CustomFacebookFeed;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}


use CustomFacebookFeed\Helpers\Util;
use CustomFacebookFeed\Builder\CFF_Db;
use CustomFacebookFeed\CFF_Utils;

class CFF_Blocks
{
	/**
	 * Indicates if current integration is allowed to load.
	 *
	 * @since 1.8
	 *
	 * @return bool
	 */
	public function allow_load()
	{
		return function_exists('register_block_type');
	}

	/**
	 * Loads an integration.
	 *
	 * @since 2.3
	 */
	public function load()
	{
		$this->hooks();
	}

	/**
	 * Integration hooks.
	 *
	 * @since 2.3
	 */
	protected function hooks()
	{
		add_action('init', array( $this, 'register_block' ));
		add_action('enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ));

		/*
		* Add smashballoon category and Facebook Feed Block
		* @since 4.3.7
		*/
		add_filter('block_categories_all', array( $this, 'register_block_category' ), 10, 2);
		add_action('init', array( $this, 'register_facebook_feed_block' ));
		add_action('enqueue_block_editor_assets', array( $this, 'enqueue_facebook_feed_block_editor_assets' ));
		add_action('enqueue_block_editor_assets', array( $this, 'set_script_translations' ));
	}

	/**
	 * Register Custom Facebook Feed Gutenberg block on the backend.
	 *
	 * @since 2.3
	 */
	public function register_block()
	{

		wp_register_style(
			'cff-blocks-styles',
			trailingslashit(CFF_PLUGIN_URL) . 'assets/css/cff-blocks.css',
			array( 'wp-edit-blocks' ),
			CFFVER
		);

		$attributes = array(
			'shortcodeSettings' => array(
				'type' => 'string',
			),
			'noNewChanges' => array(
				'type' => 'boolean',
			),
			'executed' => array(
				'type' => 'boolean',
			)
		);

		register_block_type(
			'cff/cff-feed-block',
			array(
				'attributes'      => $attributes,
				'render_callback' => array( $this, 'get_feed_html' ),
			)
		);
	}

	/**
	 * Load Custom Facebook Feed Gutenberg block scripts.
	 *
	 * @since 2.3
	 */
	public function enqueue_block_editor_assets()
	{
		$access_token = get_option('cff_access_token');

		wp_enqueue_style('cff-blocks-styles');
		wp_enqueue_script(
			'cff-feed-block',
			trailingslashit(CFF_PLUGIN_URL) . 'assets/js/cff-blocks.js',
			array( 'wp-blocks', 'wp-i18n', 'wp-element' ),
			CFFVER,
			true
		);

		$shortcodeSettings = '';

		$i18n = array(
			'addSettings'         => esc_html__('Add Settings', 'custom-facebook-feed'),
			'shortcodeSettings'   => esc_html__('Shortcode Settings', 'custom-facebook-feed'),
			'example'             => esc_html__('Example', 'custom-facebook-feed'),
			'preview'             => esc_html__('Apply Changes', 'custom-facebook-feed'),

		);

		if (! empty($_GET['cff_wizard'])) {
			$shortcodeSettings = 'feed="' . (int)sanitize_text_field(wp_unslash($_GET['cff_wizard'])) . '"';
		}

		wp_localize_script(
			'cff-feed-block',
			'cff_block_editor',
			array(
				'wpnonce'  => wp_create_nonce('facebook-blocks'),
				'canShowFeed' => ! empty($access_token),
				'configureLink' => get_admin_url() . '?page=cff-top',
				'shortcodeSettings'    => $shortcodeSettings,
				'i18n'     => $i18n,
			)
		);


		\cff_main_pro()->enqueue_styles_assets();
		\cff_main_pro()->enqueue_scripts_assets();
	}

	/**
	 * Get form HTML to display in a Custom Facebook Feed Gutenberg block.
	 *
	 * @param array $attr Attributes passed by Custom Facebook Feed Gutenberg block.
	 *
	 * @since 2.3
	 *
	 * @return string
	 */
	public function get_feed_html($attr)
	{
		$cff_statuses = get_option('cff_statuses', array());

		$feeds_count = CFF_Db::feeds_count();
		$shortcode_settings = isset($attr['shortcodeSettings']) ? $attr['shortcodeSettings'] : '';
		if ($feeds_count <= 0) {
			return $this->plain_block_design(empty(cff_main_pro()->cff_license_handler->get_license_key) ? 'inactive' : 'expired');
		}

		$return = '';
		$return .= $this->get_license_expired_notice();

		if (empty($cff_statuses['support_legacy_shortcode'])) {
			if (empty($shortcode_settings) || strpos($shortcode_settings, 'feed=') === false) {
				$feeds = \CustomFacebookFeed\Builder\CFF_Feed_Builder::get_feed_list();
				if (! empty($feeds[0]['id'])) {
					$shortcode_settings = 'feed="' . (int) $feeds[0]['id'] . '"';
				}
			}
		}

		$shortcode_settings = str_replace(array( '[custom-facebook-feed', ']' ), '', $shortcode_settings);

		$return .= do_shortcode('[custom-facebook-feed ' . $shortcode_settings . ']');

		return $return;
	}

	public function get_license_expired_notice()
	{
		// Check that the license exists and the user hasn't already clicked to ignore the message
		if (empty(cff_main_pro()->cff_license_handler->get_license_key)) {
			return $this->get_license_expired_notice_content('inactive');
		}
		// If license not expired then return;
		if (!cff_main_pro()->cff_license_handler->is_license_expired) {
			return;
		}
		// Grace period ended?
		if (! cff_main_pro()->cff_license_handler->is_license_grace_period_ended(true)) {
			return;
		}

		return $this->get_license_expired_notice_content();
	}

	/**
	 * Output the license expired notice content on top of the embed block
	 *
	 * @since 4.4.0
	 */
	public function get_license_expired_notice_content($license_state = 'expired')
	{
		if (!is_admin() && !defined('REST_REQUEST')) {
			return;
		}

		$output = '<div class="cff-block-license-expired-notice-ctn cff-bln-license-state-' . $license_state . '">';
			$output .= '<div class="cff-blen-header">';
				$output .= '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M7.99984 6C7.4694 6 6.9607 6.21071 6.58562 6.58579C6.21055 6.96086 5.99984 7.46957 5.99984 8C5.99984 8.53043 6.21055 9.03914 6.58562 9.41421C6.9607 9.78929 7.4694 10 7.99984 10C8.53027 10 9.03898 9.78929 9.41405 9.41421C9.78912 9.03914 9.99984 8.53043 9.99984 8C9.99984 7.46957 9.78912 6.96086 9.41405 6.58579C9.03898 6.21071 8.53027 6 7.99984 6ZM7.99984 11.3333C7.11578 11.3333 6.26794 10.9821 5.64281 10.357C5.01769 9.7319 4.6665 8.88406 4.6665 8C4.6665 7.11595 5.01769 6.2681 5.64281 5.64298C6.26794 5.01786 7.11578 4.66667 7.99984 4.66667C8.88389 4.66667 9.73174 5.01786 10.3569 5.64298C10.982 6.2681 11.3332 7.11595 11.3332 8C11.3332 8.88406 10.982 9.7319 10.3569 10.357C9.73174 10.9821 8.88389 11.3333 7.99984 11.3333ZM7.99984 3C4.6665 3 1.81984 5.07333 0.666504 8C1.81984 10.9267 4.6665 13 7.99984 13C11.3332 13 14.1798 10.9267 15.3332 8C14.1798 5.07333 11.3332 3 7.99984 3Z" fill="#141B38"></path></svg>';
				$output .= '<span>' . __('Only Visible to WordPress Admins', 'custom-facebook-feed') . '</span>';
			$output .= '</div>';
			$output .= '<div class="cff-blen-resolve">';
				$output .= '<div class="cff-left">';
					$output .= ' <svg viewBox="0 0 14 14"><path d="M6.33203 5.00004H7.66536V3.66671H6.33203V5.00004ZM6.9987 12.3334C4.0587 12.3334 1.66536 9.94004 1.66536 7.00004C1.66536 4.06004 4.0587 1.66671 6.9987 1.66671C9.9387 1.66671 12.332 4.06004 12.332 7.00004C12.332 9.94004 9.9387 12.3334 6.9987 12.3334ZM6.9987 0.333374C6.12322 0.333374 5.25631 0.505812 4.44747 0.840844C3.63864 1.17588 2.90371 1.66694 2.28465 2.286C1.03441 3.53624 0.332031 5.23193 0.332031 7.00004C0.332031 8.76815 1.03441 10.4638 2.28465 11.7141C2.90371 12.3331 3.63864 12.8242 4.44747 13.1592C5.25631 13.4943 6.12322 13.6667 6.9987 13.6667C8.76681 13.6667 10.4625 12.9643 11.7127 11.7141C12.963 10.4638 13.6654 8.76815 13.6654 7.00004C13.6654 6.12456 13.4929 5.25766 13.1579 4.44882C12.8229 3.63998 12.3318 2.90505 11.7127 2.286C11.0937 1.66694 10.3588 1.17588 9.54992 0.840844C8.74108 0.505812 7.87418 0.333374 6.9987 0.333374ZM6.33203 10.3334H7.66536V6.33337H6.33203V10.3334Z"></path></svg> ';
		if ($license_state == 'inactive') {
			$output .= '<span>' . __('Your license key is inactive. Activate it to enable Pro features.', 'custom-facebook-feed') . '</span>';
		} else {
			$output .= '<span>' . __('Your license has expired! Renew it to reactivate Pro features.', 'custom-facebook-feed') . '</span>';
		}
				$output .= '</div>';
				$output .= '<div class="cff-right">';
					$output .= '<a href="' . cff_main_pro()->cff_license_handler->get_renew_url($license_state) . '" target="_blank">' . __('Resolve Now', 'custom-facebook-feed') . '</a>';
					$output .= '<svg xmlns="http://www.w3.org/2000/svg" width="7" height="10" viewBox="0 0 7 10" fill="none"><path d="M1.3332 0L0.158203 1.175L3.97487 5L0.158203 8.825L1.3332 10L6.3332 5L1.3332 0Z" fill="#0068A0"></path></svg> ';
				$output .= '</div>';
			$output .= '</div>';
		$output .= '</div>';

		return $output;
	}

	/**
	 * Plain block design when theres no feeds.
	 *
	 * @since 4.4.0
	 */
	public function plain_block_design($license_state = 'expired')
	{
		if (!is_admin() && !defined('REST_REQUEST')) {
			return;
		}
		$other_plugins = $this->get_others_plugins();
		$should_display_license_notice = cff_main_pro()->cff_license_handler->should_disable_pro_features;
		$output = '<div class="cff-license-expired-plain-block-wrapper ' . $license_state . '">';

		if ($should_display_license_notice) :
			$output .= '<div class="cff-lepb-header">
				<div class="sb-left">';
					$output .= '<svg viewBox="0 0 14 14"><path d="M6.33203 5.00004H7.66536V3.66671H6.33203V5.00004ZM6.9987 12.3334C4.0587 12.3334 1.66536 9.94004 1.66536 7.00004C1.66536 4.06004 4.0587 1.66671 6.9987 1.66671C9.9387 1.66671 12.332 4.06004 12.332 7.00004C12.332 9.94004 9.9387 12.3334 6.9987 12.3334ZM6.9987 0.333374C6.12322 0.333374 5.25631 0.505812 4.44747 0.840844C3.63864 1.17588 2.90371 1.66694 2.28465 2.286C1.03441 3.53624 0.332031 5.23193 0.332031 7.00004C0.332031 8.76815 1.03441 10.4638 2.28465 11.7141C2.90371 12.3331 3.63864 12.8242 4.44747 13.1592C5.25631 13.4943 6.12322 13.6667 6.9987 13.6667C8.76681 13.6667 10.4625 12.9643 11.7127 11.7141C12.963 10.4638 13.6654 8.76815 13.6654 7.00004C13.6654 6.12456 13.4929 5.25766 13.1579 4.44882C12.8229 3.63998 12.3318 2.90505 11.7127 2.286C11.0937 1.66694 10.3588 1.17588 9.54992 0.840844C8.74108 0.505812 7.87418 0.333374 6.9987 0.333374ZM6.33203 10.3334H7.66536V6.33337H6.33203V10.3334Z"></path></svg> ';
			if ($license_state == 'expired') {
				$output .= sprintf('<p>%s</p>', __('Your license has expired! Renew it to reactivate Pro features.', 'custom-facebook-feed'));
			} else {
				$output .= sprintf('<p>%s</p>', __('Your license key is inactive. Activate it to enable Pro features.', 'custom-facebook-feed'));
			}
			$output .= '</div>
				<div class="sb-right">
					<a href="' . cff_main_pro()->cff_license_handler->get_renew_url($license_state) . '">
						Resolve Now
						<svg xmlns="http://www.w3.org/2000/svg" width="7" height="10" viewBox="0 0 7 10" fill="none"><path d="M1.3332 0L0.158203 1.175L3.97487 5L0.158203 8.825L1.3332 10L6.3332 5L1.3332 0Z" fill="#0068A0"></path></svg>
					</a>
				</div>
			</div>';
		endif;
			$output .= '<div class="cff-lepb-body">
				<svg xmlns="http://www.w3.org/2000/svg" width="86" height="83" viewBox="0 0 86 83" fill="none"><rect x="1" y="4.43494" width="65.6329" height="65.6329" rx="12" transform="rotate(-3 1 4.43494)" fill="white"></rect><rect x="1" y="4.43494" width="65.6329" height="65.6329" rx="12" transform="rotate(-3 1 4.43494)" stroke="#CED0D9" stroke-width="2.5003"></rect><path d="M54.2493 60.4452C54.2493 68.7888 59.2323 75.931 66.0155 77.1715L65.6194 78.4257L65.2177 79.6978L66.547 79.5848L71.0345 79.2031L72.7237 79.0594L71.6502 77.7473L71.0255 76.9839C77.3765 75.3291 81.9509 68.4326 81.9509 60.4452C81.9509 51.2906 75.9193 43.5316 68.1014 43.5316C60.2838 43.5316 54.2493 51.2904 54.2493 60.4452Z" fill="#FE544F" stroke="white" stroke-width="1.78661"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M71.1909 50.0573L71.8144 56.491L78.2752 56.6766L73.6016 61.0221L77.2929 66.3611L71.0743 65.1921L69.189 71.4108L66.3266 65.842L60.5468 68.4904L62.7702 62.5204L57.1333 59.6772L63.1675 57.7366L61.5024 51.7922L67.2111 55.0468L71.1909 50.0573Z" fill="white"></path><path d="M34.8208 18.4374C26.2378 18.8872 19.5825 26.2621 20.0348 34.8919C20.4437 42.6946 26.4947 48.8716 34.0155 49.651L33.443 38.7272L29.4792 38.935L29.242 34.4094L33.2058 34.2017L33.0251 30.7528C32.8198 26.8359 35.0321 24.5605 38.6058 24.3732C40.3068 24.284 42.1014 24.4873 42.1014 24.4873L42.3034 28.3418L40.3371 28.4449C38.402 28.5463 37.8564 29.7798 37.921 31.0127L38.0747 33.9465L42.413 33.7191L41.948 38.2815L38.3119 38.4721L38.8844 49.3959C42.5313 48.6224 45.7815 46.5706 48.0483 43.6109C50.3151 40.6513 51.4492 36.9787 51.2457 33.2562C50.7935 24.6264 43.4038 17.9876 34.8208 18.4374Z" fill="#006BFA"></path></svg>
				<p class="cff-block-body-title">Get started with your first feed from <br/> your Instagram profile</p>';

		$output .= sprintf(
			'<a href="%s" class="cff-btn cff-btn-blue">%s <svg xmlns="http://www.w3.org/2000/svg" width="7" height="10" viewBox="0 0 7 10" fill="none"><path d="M1.3332 0L0.158203 1.175L3.97487 5L0.158203 8.825L1.3332 10L6.3332 5L1.3332 0Z" fill="#0068A0"></path></svg> </a>',
			admin_url('admin.php?page=cff-feed-builder'),
			__('Create a Facebook Feed', 'custom-facebook-feed')
		);
		$output .= '</div>
			<div class="cff-lepd-footer">
				<p class="cff-lepd-footer-title">Did you know? </p>
				<p>You can add posts from ' . $other_plugins . ' using our free plugins</p>
			</div>
		</div>';

		return $output;
	}


	/**
	 * Get other Smash Balloon plugins list
	 *
	 * @since 4.4.0
	 */
	public function get_others_plugins()
	{
		$active_plugins = Util::get_sb_active_plugins_info();

		$other_plugins = array(
			'is_instagram_installed' => array(
				'title' => 'Instagram',
				'url'	=> 'https://smashballoon.com/instagram-feed/?utm_campaign=youtube-pro&utm_source=block-feed-embed&utm_medium=did-you-know',
			),
			'is_facebook_installed' => array(
				'title' => 'Facebook',
				'url'	=> 'https://smashballoon.com/custom-facebook-feed/?utm_campaign=youtube-pro&utm_source=block-feed-embed&utm_medium=did-you-know',
			),
			'is_twitter_installed' => array(
				'title' => 'Twitter',
				'url'	=> 'https://smashballoon.com/custom-twitter-feeds/?utm_campaign=youtube-pro&utm_source=block-feed-embed&utm_medium=did-you-know',
			),
			'is_youtube_installed' => array(
				'title' => 'YouTube',
				'url'	=> 'https://smashballoon.com/youtube-feed/?utm_campaign=youtube-pro&utm_source=block-feed-embed&utm_medium=did-you-know',
			),
		);

		if (! empty($active_plugins)) {
			foreach ($active_plugins as $name => $plugin) {
				if ($plugin != false) {
					unset($other_plugins[$name]);
				}
			}
		}

		$other_plugins_html = array();
		foreach ($other_plugins as $plugin) {
			$other_plugins_html[] = '<a href="' . $plugin['url'] . '">' . $plugin['title'] . '</a>';
		}

		return \implode(", ", $other_plugins_html);
	}

	/**
	 * Checking if is Gutenberg REST API call.
	 *
	 * @since 2.3
	 *
	 * @return bool True if is Gutenberg REST API call.
	 */
	public static function is_gb_editor()
	{

		// TODO: Find a better way to check if is GB editor API call.
		return defined( 'REST_REQUEST' ) && REST_REQUEST && ! empty( $_REQUEST['context'] ) && 'edit' === $_REQUEST['context']; // phpcs:ignore
	}

	/**
	 * Register Block Category
	 *
	 * @since 4.3.7
	 */
	public function register_block_category($categories, $context)
	{
		$exists = array_search('smashballoon', array_column($categories, 'slug'));

		if ($exists !== false) {
			return $categories;
		}

		return array_merge(
			$categories,
			array(
				array(
					'slug' => 'smashballoon',
					'title' => __('Smash Balloon', 'custom-facebook-feed'),
				),
			)
		);
	}

	/**
	 * Register Block
	 *
	 * @since 4.3.7
	 */
	public function register_facebook_feed_block()
	{
		register_block_type(
			trailingslashit(CFF_PLUGIN_DIR) . 'assets/dist/sbf-feed',
			array(
				'render_callback' => array( $this, 'render_facebook_feed_block' ),
			)
		);
	}

	/**
	 * Render Block
	 *
	 * @since 4.3.7
	 */
	public function render_facebook_feed_block($attributes)
	{
		$className = isset($attributes['className']) ? $attributes['className'] : '';
		$content = '';

		if (isset($attributes['feedId'])) {
			$content = do_shortcode('[custom-facebook-feed feed=' . (int) $attributes['feedId'] . ' class=' . esc_attr($className) . ']');
		}

		return $content;
	}

	/**
	 * Enqueue Block Assets
	 *
	 * @since 4.3.7
	 */
	public function enqueue_facebook_feed_block_editor_assets()
	{
		$asset_file = include_once trailingslashit(CFF_PLUGIN_DIR) . 'assets/dist/blocks.asset.php';

		wp_enqueue_script(
			'cff-feed-block-editor',
			trailingslashit(CFF_PLUGIN_URL) . 'assets/dist/blocks.js',
			$asset_file['dependencies'],
			$asset_file['version'],
			true
		);

		wp_enqueue_style(
			'cff-feed-block-editor',
			trailingslashit(CFF_PLUGIN_URL) . 'assets/dist/blocks.css',
			array(),
			$asset_file['version']
		);

		wp_localize_script(
			'cff-feed-block-editor',
			'cff_feed_block_editor',
			array(
				'feeds' => CFF_Db::all_feeds_query(),
				'feed_url' => admin_url('admin.php?page=cff-feed-builder'),
				'plugins_info' => Util::get_smash_plugins_status_info(),
				'has_facebook_feed_block' => $this->has_facebook_feed_block(),
				'is_pro_active' => CFF_Utils::cff_is_pro_version(),
				'nonce'         => wp_create_nonce('cff-admin'),
			)
		);
	}

	/**
	 * Set Script Translations
	 *
	 * @since 4.3.7
	 */
	public function set_script_translations()
	{
		wp_set_script_translations('cff-feed-block-editor', 'custom-facebook-feed', CFF_PLUGIN_DIR . 'languages');
	}

	/**
	 * Check if the post has a Facebook Feed block
	 *
	 * @since 4.3.7
	 */
	public function has_facebook_feed_block()
	{
		return has_block('cff/cff-feed-block');
	}
}
