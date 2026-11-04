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
	 * Emails a contact form submission to the configured recipient.
	 *
	 * @param array $data     Sanitized submission fields: name, email, phone, subject, message.
	 * @param array $settings Plugin settings.
	 * @return bool True when wp_mail() accepted the message.
	 */
	public static function send_contact_email( array $data, array $settings ) {
		$site_name = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );


		$subject = sprintf(
			/* translators: 1: site name, 2: message subject */
			__( '[%1$s] New contact message: %2$s', 'smart-contact-hub' ),
			$site_name,
			$data['subject']
		);


		$phone = '' !== $data['phone'] ? $data['phone'] : __( 'Not provided', 'smart-contact-hub' );


		$lines = array(
			__( 'You received a new message from your website contact form.', 'smart-contact-hub' ),
			'',
			__( 'Name:', 'smart-contact-hub' ) . ' ' . $data['name'],
			__( 'Email:', 'smart-contact-hub' ) . ' ' . $data['email'],
			__( 'Phone:', 'smart-contact-hub' ) . ' ' . $phone,
			__( 'Subject:', 'smart-contact-hub' ) . ' ' . $data['subject'],
			__( 'Submitted:', 'smart-contact-hub' ) . ' ' . current_time( 'mysql' ),
			'',
			__( 'Message:', 'smart-contact-hub' ),
			$data['message'],
		);


		$reply_name = str_replace( array( '"', '<', '>', ',' ), '', $data['name'] );
		$headers    = array(
			'Content-Type: text/plain; charset=UTF-8',
			'Reply-To: ' . $reply_name . ' <' . $data['email'] . '>',
		);


		return wp_mail( $settings['recipient_email'], $subject, implode( "\n", $lines ), $headers );
	}
}

