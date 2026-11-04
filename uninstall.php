<?php
/**
 * Removes plugin data when the plugin is deleted from the WordPress admin.
 *
 * @package SmartContactHub
 */


if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}


delete_option( 'sch_settings' );

