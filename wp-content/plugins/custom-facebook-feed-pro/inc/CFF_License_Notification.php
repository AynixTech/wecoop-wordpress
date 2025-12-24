<?php

/**
 * Class CFF_License_Notification
 *
 * This class displays license related notices in front end
 *
 * @since 4.4
 */

namespace CustomFacebookFeed;

if (! defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

use CustomFacebookFeed\Helpers\Util;
use CustomFacebookFeed\Builder\CFF_Db;
use CustomFacebookFeed\Builder\CFF_Feed_Builder;

class CFF_License_Notification
{
	protected $db;

	public function __construct()
	{
		$this->db = new CFF_Db();
		$this->register();
	}

	public function register()
	{
		add_action('wp_footer', [$this, 'cff_frontend_license_error'], 300);
		add_action('wp_ajax_cff_hide_frontend_license_error', [$this, 'hide_frontend_license_error'], 10);
	}

	/**
	 * Hide the frontend license error message for a day
	 *
	 * @since 2.0.3
	 */
	public function hide_frontend_license_error()
	{
		check_ajax_referer('cff_nonce', 'nonce');
		$cap = current_user_can('manage_custom_facebook_feed_options') ? 'manage_custom_facebook_feed_options' : 'manage_options';
		$cap = apply_filters('cff_settings_pages_capability', $cap);
		if (!current_user_can($cap)) {
			return;
		}

		set_transient('cff_license_error_notice', true, DAY_IN_SECONDS);
		wp_die();
	}

	public function cff_frontend_license_error()
	{
		// Don't do anything for guests.
		if (! is_user_logged_in()) {
			return;
		}
		if (! current_user_can(Util::capablityCheck())) {
			return;
		}
		// Check that the license exists and the user hasn't already clicked to ignore the message
		if (empty(cff_main_pro()->cff_license_handler->get_license_key)) {
			$this->cff_frontend_license_error_content('inactive');
			return;
		}
		// If license not expired then return;
		if (!cff_main_pro()->cff_license_handler->is_license_expired) {
			return;
		}
		if (cff_main_pro()->cff_license_handler->is_license_grace_period_ended(true)) {
			$this->cff_frontend_license_error_content();
		}
		return;
	}

	/**
	 * Output frontend license error HTML content
	 *
	 * @since 6.2.0
	 */
	public function cff_frontend_license_error_content($license_state = 'expired')
	{
		$feeds_count = $this->db->feeds_count();
		if ($feeds_count <= 0) {
			return;
		}

		$should_display_license_error_notice = get_transient('cff_license_error_notice');
		if ($should_display_license_error_notice) {
			return;
		}
	?>
			<div id="cff-fr-ce-license-error" class="cff-critical-error cff-frontend-license-notice cff-ce-license-<?php echo $license_state; ?>">
				<div class="cff-fln-header">
					<span class="sb-left">
						<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M7.99984 6C7.4694 6 6.9607 6.21071 6.58562 6.58579C6.21055 6.96086 5.99984 7.46957 5.99984 8C5.99984 8.53043 6.21055 9.03914 6.58562 9.41421C6.9607 9.78929 7.4694 10 7.99984 10C8.53027 10 9.03898 9.78929 9.41405 9.41421C9.78912 9.03914 9.99984 8.53043 9.99984 8C9.99984 7.46957 9.78912 6.96086 9.41405 6.58579C9.03898 6.21071 8.53027 6 7.99984 6ZM7.99984 11.3333C7.11578 11.3333 6.26794 10.9821 5.64281 10.357C5.01769 9.7319 4.6665 8.88406 4.6665 8C4.6665 7.11595 5.01769 6.2681 5.64281 5.64298C6.26794 5.01786 7.11578 4.66667 7.99984 4.66667C8.88389 4.66667 9.73174 5.01786 10.3569 5.64298C10.982 6.2681 11.3332 7.11595 11.3332 8C11.3332 8.88406 10.982 9.7319 10.3569 10.357C9.73174 10.9821 8.88389 11.3333 7.99984 11.3333ZM7.99984 3C4.6665 3 1.81984 5.07333 0.666504 8C1.81984 10.9267 4.6665 13 7.99984 13C11.3332 13 14.1798 10.9267 15.3332 8C14.1798 5.07333 11.3332 3 7.99984 3Z" fill="#141B38"></path></svg>
						<span class="sb-text">Only Visible to WordPress Admins</span>
					</span>
					<span id="cff-frce-hide-license-error" class="sb-close">
						<svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 10 10" fill="none"><path d="M9.66671 1.27334L8.72671 0.333344L5.00004 4.06001L1.27337 0.333344L0.333374 1.27334L4.06004 5.00001L0.333374 8.72668L1.27337 9.66668L5.00004 5.94001L8.72671 9.66668L9.66671 8.72668L5.94004 5.00001L9.66671 1.27334Z" fill="#841919"></path></svg>
					</span>
				</div>
				<div class="cff-fln-body">
					<svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M19.198 4.4374C10.615 4.88722 3.95971 12.2621 4.41198 20.8919C4.82091 28.6946 10.8719 34.8716 18.3927 35.651L17.8202 24.7272L13.8564 24.935L13.6192 20.4094L17.583 20.2017L17.4022 16.7528C17.197 12.8359 19.4093 10.5605 22.983 10.3732C24.684 10.284 26.4785 10.4873 26.4785 10.4873L26.6805 14.3418L24.7142 14.4449C22.7792 14.5463 22.2335 15.7798 22.2981 17.0127L22.4519 19.9465L26.7902 19.7191L26.3251 24.2815L22.6891 24.4721L23.2616 35.3959C26.9085 34.6224 30.1587 32.5706 32.4255 29.6109C34.6923 26.6513 35.8264 22.9787 35.6229 19.2562C35.1706 10.6264 27.781 3.98759 19.198 4.4374Z" fill="#006BFA"/></svg>
					<div class="cff-fln-expired-text">
						<p>
							<?php
								printf(
									__('Your Facebook Feed Pro license key %s', 'custom-facebook-feed'),
									$license_state == 'expired' ? 'has ' . $license_state : 'is ' . $license_state
								);
							?>
							<a href="<?php echo $this->get_renew_url($license_state); ?>">Resolve Now
								<svg xmlns="http://www.w3.org/2000/svg" width="7" height="10" viewBox="0 0 7 10" fill="none"><path d="M1.3332 0L0.158203 1.175L3.97487 5L0.158203 8.825L1.3332 10L6.3332 5L1.3332 0Z" fill="#0068A0"></path></svg>
							</a>
						</p>
					</div>
				</div>
			</div>
		<?php
	}

	/**
	 * SBY Get Renew License URL
	 *
	 * @since 2.0
	 *
	 * @return string $url
	 */
	public function get_renew_url($license_state = 'expired')
	{
		global $cff_download_id;
		if ($license_state == 'inactive') {
			return admin_url('admin.php?page=cff-settings&focus=license');
		}
		$license_key = cff_main_pro()->cff_license_handler->get_license_key;

		$url = sprintf(
			'https://smashballoon.com/checkout/?edd_license_key=%s&download_id=%s&utm_campaign=instagram-pro&utm_source=expired-notice&utm_medium=renew-license',
			$license_key,
			$cff_download_id
		);

		return $url;
	}
}