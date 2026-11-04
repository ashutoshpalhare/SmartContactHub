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


	public static function init() {
		add_shortcode( self::SHORTCODE, array( __CLASS__, 'render_shortcode' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'maybe_enqueue_assets' ) );
		add_action( 'wp_ajax_' . self::AJAX_ACTION, array( __CLASS__, 'handle_submission' ) );
		add_action( 'wp_ajax_nopriv_' . self::AJAX_ACTION, array( __CLASS__, 'handle_submission' ) );
	}


	/**
	 * Loads assets early on singular content that contains the shortcode.
	 * Pages rendering the shortcode elsewhere (widgets, templates) load them in render_shortcode().
	 */
	public static function maybe_enqueue_assets() {
		global $post;


		if ( $post instanceof WP_Post && has_shortcode( $post->post_content, self::SHORTCODE ) ) {
			self::enqueue_assets();
		}
	}


	public static function enqueue_assets() {
		wp_enqueue_style( 'sch-frontend', SCH_PLUGIN_URL . 'assets/css/frontend.css', array(), SCH_VERSION );
		wp_enqueue_script( 'sch-frontend', SCH_PLUGIN_URL . 'assets/js/frontend.js', array(), SCH_VERSION, true );
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
	 * Outputs the contact form markup for [smart_contact_hub].
	 *
	 * @return string
	 */
	public static function render_shortcode() {
		self::enqueue_assets();


		$settings = sch_get_settings();
		$uid      = wp_unique_id( 'sch-' );


		ob_start();
		?>
		<div class="sch-form-wrap">
			<div class="sch-card">
				<div class="sch-form-panel">
					<h2 class="sch-title"><?php echo esc_html( $settings['form_title'] ); ?></h2>


					<form class="sch-form" method="post">
						<div class="sch-grid">
							<div class="sch-field">
								<label for="<?php echo esc_attr( $uid ); ?>-name">
									<?php esc_html_e( 'Name', 'smart-contact-hub' ); ?> <span class="sch-required" aria-hidden="true">*</span>