<?php
/**
 * Shortcode rendering and AJAX submission handling for the contact form.
 *
 * @package SmartContactHub
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SCH_Frontend {

	const SHORTCODE   = 'smart_contact_hub';
	const AJAX_ACTION = 'sch_submit_form';
	const NONCE_NAME  = 'sch_nonce';
	const HONEYPOT    = 'sch_website';
	const RATE_LIMIT  = 20;

	/**
	 * Register frontend hooks.
	 */
	public static function init() {
		add_shortcode( self::SHORTCODE, array( __CLASS__, 'render_shortcode' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'maybe_enqueue_assets' ) );
		add_action( 'wp_ajax_' . self::AJAX_ACTION, array( __CLASS__, 'handle_submission' ) );
		add_action( 'wp_ajax_nopriv_' . self::AJAX_ACTION, array( __CLASS__, 'handle_submission' ) );
	}

	/**
	 * Enqueue assets when the shortcode appears in post content.
	 */
	public static function maybe_enqueue_assets() {
		global $post;

		if ( $post instanceof WP_Post && has_shortcode( $post->post_content, self::SHORTCODE ) ) {
			self::enqueue_assets();
		}
	}

	/**
	 * Register frontend assets and their localized configuration.
	 */
	public static function enqueue_assets() {
		wp_enqueue_style(
			'sch-frontend',
			SCH_PLUGIN_URL . 'assets/css/frontend.css',
			array(),
			SCH_VERSION
		);

		wp_enqueue_script(
			'sch-frontend',
			SCH_PLUGIN_URL . 'assets/js/frontend.js',
			array(),
			SCH_VERSION,
			true
		);

		wp_localize_script(
			'sch-frontend',
			'schFrontend',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'i18n'    => array(
					'sending'      => __( 'Sending...', 'smart-contact-hub' ),
					'error'        => __( 'Something went wrong. Please try again.', 'smart-contact-hub' ),
					'networkError' => __( 'Network error. Please check your connection and try again.', 'smart-contact-hub' ),
				),
			)
		);
	}

	/**
	 * Render the contact form shortcode.
	 *
	 * @return string
	 */
	public static function render_shortcode() {
		self::enqueue_assets();

		$settings = sch_get_settings();
		$uid      = wp_unique_id( 'sch-' );

		ob_start();
		?>
		<div class="sch-form-wrap" data-sch-form>
			<div class="sch-card">
				<div class="sch-form-panel">
					<h2 class="sch-title"><?php echo esc_html( $settings['form_title'] ); ?></h2>
					<form class="sch-form" method="post" novalidate>
						<input type="hidden" name="action" value="<?php echo esc_attr( self::AJAX_ACTION ); ?>">
						<?php echo wp_nonce_field( self::AJAX_ACTION, self::NONCE_NAME, true, false ); ?>
						<div class="sch-grid">
							<div class="sch-field">
								<label for="<?php echo esc_attr( $uid ); ?>-name"><?php esc_html_e( 'Name', 'smart-contact-hub' ); ?> <span class="sch-required" aria-hidden="true">*</span></label>
								<input id="<?php echo esc_attr( $uid ); ?>-name" name="name" type="text" autocomplete="name" maxlength="120" required>
							</div>
							<div class="sch-field">
								<label for="<?php echo esc_attr( $uid ); ?>-email"><?php esc_html_e( 'Email', 'smart-contact-hub' ); ?> <span class="sch-required" aria-hidden="true">*</span></label>
								<input id="<?php echo esc_attr( $uid ); ?>-email" name="email" type="email" autocomplete="email" maxlength="190" required>
							</div>
							<div class="sch-field">
								<label for="<?php echo esc_attr( $uid ); ?>-phone"><?php esc_html_e( 'Phone', 'smart-contact-hub' ); ?></label>
								<input id="<?php echo esc_attr( $uid ); ?>-phone" name="phone" type="tel" autocomplete="tel" maxlength="40">
							</div>
							<div class="sch-field">
								<label for="<?php echo esc_attr( $uid ); ?>-subject"><?php esc_html_e( 'Subject', 'smart-contact-hub' ); ?> <span class="sch-required" aria-hidden="true">*</span></label>
								<input id="<?php echo esc_attr( $uid ); ?>-subject" name="subject" type="text" maxlength="160" required>
							</div>
							<div class="sch-field sch-field--full">
								<label for="<?php echo esc_attr( $uid ); ?>-message"><?php esc_html_e( 'Message', 'smart-contact-hub' ); ?> <span class="sch-required" aria-hidden="true">*</span></label>
								<textarea id="<?php echo esc_attr( $uid ); ?>-message" name="message" rows="6" maxlength="5000" required></textarea>
							</div>
							<div class="sch-field sch-field--trap" aria-hidden="true">
								<label for="<?php echo esc_attr( $uid ); ?>-website"><?php esc_html_e( 'Leave this field empty', 'smart-contact-hub' ); ?></label>
								<input id="<?php echo esc_attr( $uid ); ?>-website" name="<?php echo esc_attr( self::HONEYPOT ); ?>" type="text" tabindex="-1" autocomplete="off">
							</div>
						</div>
						<p class="sch-status" role="status" aria-live="polite" aria-atomic="true" hidden></p>
						<button class="sch-submit" type="submit"><span class="sch-submit-text"><?php esc_html_e( 'Send Message', 'smart-contact-hub' ); ?></span></button>
					</form>
				</div>
				<div class="sch-success" role="status" tabindex="-1" hidden>
					<p class="sch-success-text"><?php echo esc_html( $settings['success_message'] ); ?></p>
					<a class="sch-whatsapp" href="#" target="_blank" rel="noopener noreferrer" hidden><?php esc_html_e( 'Continue on WhatsApp', 'smart-contact-hub' ); ?></a>
					<button class="sch-reset" type="button"><?php esc_html_e( 'Send another message', 'smart-contact-hub' ); ?></button>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Validate, rate-limit, and process an AJAX form submission.
	 */
	public static function handle_submission() {
		if ( ! check_ajax_referer( self::AJAX_ACTION, self::NONCE_NAME, false ) ) {
			wp_send_json_error(
				array( 'message' => __( 'Your session has expired. Refresh the page and try again.', 'smart-contact-hub' ) ),
				403
			);
		}

		$honeypot = isset( $_POST[ self::HONEYPOT ] )
			? sanitize_text_field( wp_unslash( $_POST[ self::HONEYPOT ] ) )
			: '';

		if ( '' !== $honeypot ) {
			wp_send_json_success( array( 'message' => sch_get_settings()['success_message'] ) );
		}

		if ( self::is_rate_limited() ) {
			wp_send_json_error(
				array( 'message' => __( 'Please wait a moment before sending another message.', 'smart-contact-hub' ) ),
				429
			);
		}

		$data = self::get_submission_data();

		if ( is_wp_error( $data ) ) {
			wp_send_json_error( array( 'message' => $data->get_error_message() ), 422 );
		}

		$settings = sch_get_settings();

		if ( ! SCH_Email::send_contact_email( $data, $settings ) ) {
			wp_send_json_error(
				array( 'message' => __( 'Your message could not be sent right now. Please try again later.', 'smart-contact-hub' ) ),
				500
			);
		}

		$response = array( 'message' => $settings['success_message'] );

		if ( 'yes' === $settings['enable_whatsapp'] && ! empty( $settings['whatsapp_number'] ) ) {
			$whatsapp_message = sprintf(
				/* translators: 1: sender name, 2: message subject */
				__( 'Hello, I just sent a contact message from your website. Name: %1$s. Subject: %2$s.', 'smart-contact-hub' ),
				$data['name'],
				$data['subject']
			);

			$response['whatsapp_url'] = 'https://wa.me/' . rawurlencode( $settings['whatsapp_number'] ) . '?text=' . rawurlencode( $whatsapp_message );
		}

		wp_send_json_success( $response );
	}

	/**
	 * Normalize and validate submitted fields.
	 *
	 * @return array|WP_Error
	 */
	private static function get_submission_data() {
		$raw = wp_unslash( $_POST );

		$data = array(
			'name'    => isset( $raw['name'] ) ? sanitize_text_field( $raw['name'] ) : '',
			'email'   => isset( $raw['email'] ) ? sanitize_email( $raw['email'] ) : '',
			'phone'   => isset( $raw['phone'] ) ? sanitize_text_field( $raw['phone'] ) : '',
			'subject' => isset( $raw['subject'] ) ? sanitize_text_field( $raw['subject'] ) : '',
			'message' => isset( $raw['message'] ) ? sanitize_textarea_field( $raw['message'] ) : '',
		);

		if ( '' === $data['name'] || '' === $data['email'] || '' === $data['subject'] || '' === $data['message'] ) {
			return new WP_Error( 'sch_required_fields', __( 'Please complete all required fields.', 'smart-contact-hub' ) );
		}

		if ( ! is_email( $data['email'] ) ) {
			return new WP_Error( 'sch_invalid_email', __( 'Enter a valid email address.', 'smart-contact-hub' ) );
		}

		$limits = array(
			'name'    => 120,
			'email'   => 190,
			'phone'   => 40,
			'subject' => 160,
			'message' => 5000,
		);

		foreach ( $limits as $field => $limit ) {
			if ( SCH_Compat::text_length( $data[ $field ] ) > $limit ) {
				return new WP_Error( 'sch_field_too_long', __( 'One or more fields exceed the allowed length.', 'smart-contact-hub' ) );
			}
		}

		return $data;
	}

	/**
	 * Apply a short transient-based submission cooldown per remote address.
	 *
	 * @return bool
	 */
	private static function is_rate_limited() {
		$remote_address = isset( $_SERVER['REMOTE_ADDR'] )
			? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) )
			: 'unknown';
		$key = 'sch_rate_' . hash_hmac( 'sha256', $remote_address, wp_salt( 'auth' ) );

		if ( get_transient( $key ) ) {
			return true;
		}

		set_transient( $key, 1, self::RATE_LIMIT );

		return false;
	}
}
