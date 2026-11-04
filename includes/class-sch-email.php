<?php
/**
 * Sends contact form submissions by email.
 *
 * @package SmartContactHub
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SCH_Email {

	/**
	 * Send a validated contact submission to the configured recipient.
	 *
	 * @param array $data     Sanitized submission fields.
	 * @param array $settings Plugin settings.
	 * @return bool
	 */
	public static function send_contact_email( array $data, array $settings ) {
		$recipient = isset( $settings['recipient_email'] )
			? sanitize_email( $settings['recipient_email'] )
			: '';

		if ( ! is_email( $recipient ) ) {
			return false;
		}

		$site_name = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
		$site_name = sanitize_text_field( $site_name );
		$subject   = sprintf(
			/* translators: 1: site name, 2: message subject */
			__( '[%1$s] New contact message: %2$s', 'smart-contact-hub' ),
			$site_name,
			sanitize_text_field( $data['subject'] )
		);

		$phone = '' !== $data['phone']
			? sanitize_text_field( $data['phone'] )
			: __( 'Not provided', 'smart-contact-hub' );

		$lines = array(
			__( 'You received a new message from your website contact form.', 'smart-contact-hub' ),
			'',
			__( 'Name:', 'smart-contact-hub' ) . ' ' . sanitize_text_field( $data['name'] ),
			__( 'Email:', 'smart-contact-hub' ) . ' ' . sanitize_email( $data['email'] ),
			__( 'Phone:', 'smart-contact-hub' ) . ' ' . $phone,
			__( 'Subject:', 'smart-contact-hub' ) . ' ' . sanitize_text_field( $data['subject'] ),
			__( 'Submitted:', 'smart-contact-hub' ) . ' ' . current_time( 'mysql' ),
			'',
			__( 'Message:', 'smart-contact-hub' ),
			sanitize_textarea_field( $data['message'] ),
		);

		$reply_name = str_replace(
			array( "\r", "\n", '"', '<', '>', ',' ),
			'',
			sanitize_text_field( $data['name'] )
		);
		$headers = array(
			'Content-Type: text/plain; charset=UTF-8',
			'Reply-To: ' . $reply_name . ' <' . sanitize_email( $data['email'] ) . '>',
		);

		return (bool) wp_mail( $recipient, $subject, implode( "\n", $lines ), $headers );
	}
}
