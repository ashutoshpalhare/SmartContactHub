<?php
/**
 * Normalize stored plugin settings before they reach admin or frontend code.
 *
 * @package SmartContactHub
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SCH_Settings {

	/**
	 * Return a complete, type-safe settings array.
	 *
	 * Database values are not guaranteed to retain the shape originally saved
	 * through the Settings API. Normalize them before rendering or using them.
	 *
	 * @param mixed $saved    Stored option value.
	 * @param array $defaults Default settings.
	 * @return array
	 */
	public static function normalize( $saved, $defaults ) {
		$saved = is_array( $saved ) ? $saved : array();

		$recipient = isset( $saved['recipient_email'] ) && is_string( $saved['recipient_email'] )
			? sanitize_email( $saved['recipient_email'] )
			: '';
		if ( ! is_email( $recipient ) || SCH_Compat::text_length( $recipient ) > 190 ) {
			$recipient = $defaults['recipient_email'];
		}

		$title = isset( $saved['form_title'] ) && is_string( $saved['form_title'] )
			? sanitize_text_field( $saved['form_title'] )
			: '';
		$title = SCH_Compat::text_substr( trim( $title ), 0, 80 );
		if ( '' === $title ) {
			$title = $defaults['form_title'];
		}

		$message = isset( $saved['success_message'] ) && is_string( $saved['success_message'] )
			? sanitize_textarea_field( $saved['success_message'] )
			: '';
		$message = SCH_Compat::text_substr( trim( $message ), 0, 240 );
		if ( '' === $message ) {
			$message = $defaults['success_message'];
		}

		$number = isset( $saved['whatsapp_number'] ) && is_string( $saved['whatsapp_number'] )
			? preg_replace( '/\\D+/', '', sanitize_text_field( $saved['whatsapp_number'] ) )
			: '';
		if ( '' !== $number && ( SCH_Compat::text_length( $number ) < 8 || SCH_Compat::text_length( $number ) > 15 ) ) {
			$number = '';
		}

		$enabled = isset( $saved['enable_whatsapp'] ) && 'yes' === $saved['enable_whatsapp'] && '' !== $number
			? 'yes'
			: 'no';

		return array(
			'recipient_email' => $recipient,
			'form_title'      => $title,
			'success_message' => $message,
			'whatsapp_number' => $number,
			'enable_whatsapp' => $enabled,
		);
	}
}
