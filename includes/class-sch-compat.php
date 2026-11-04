<?php
/**
 * Small compatibility helpers for multibyte-safe text handling.
 *
 * @package SmartContactHub
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SCH_Compat {

	/**
	 * Count Unicode characters without requiring the mbstring extension.
	 *
	 * @param string $value Text to measure.
	 * @return int
	 */
	public static function text_length( $value ) {
		$value = (string) $value;

		if ( function_exists( 'mb_strlen' ) ) {
			return mb_strlen( $value, 'UTF-8' );
		}

		$matched = preg_match_all( '/./us', $value, $characters );

		return false === $matched ? strlen( $value ) : $matched;
	}

	/**
	 * Return a Unicode-safe substring without requiring mbstring.
	 *
	 * @param string   $value  Source text.
	 * @param int      $start  Character offset.
	 * @param int|null $length Optional character length.
	 * @return string
	 */
	public static function text_substr( $value, $start, $length = null ) {
		$value = (string) $value;

		if ( function_exists( 'mb_substr' ) ) {
			return null === $length
				? mb_substr( $value, $start, null, 'UTF-8' )
				: mb_substr( $value, $start, $length, 'UTF-8' );
		}

		$matched = preg_match_all( '/./us', $value, $characters );

		if ( false === $matched ) {
			return null === $length ? substr( $value, $start ) : substr( $value, $start, $length );
		}

		$characters = $characters[0];

		return implode( '', null === $length
			? array_slice( $characters, $start )
			: array_slice( $characters, $start, $length )
		);
	}
}
