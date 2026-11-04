<?php
/**
 * Plugin Name:       Smart Contact Hub
 * Description:       A clean and modern contact form plugin with email notifications, WhatsApp redirect option, admin settings page, shortcode support, and basic spam protection.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Ashutosh Palhare
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       smart-contact-hub
 *
 * @package SmartContactHub
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


define( 'SCH_VERSION', '1.0.0' );
define( 'SCH_PLUGIN_FILE', __FILE__ );
define( 'SCH_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SCH_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SCH_OPTION_NAME', 'sch_settings' );


require_once SCH_PLUGIN_DIR . 'includes/class-sch-email.php';
require_once SCH_PLUGIN_DIR . 'includes/class-sch-admin.php';
require_once SCH_PLUGIN_DIR . 'includes/class-sch-frontend.php';


/**
 * Default values for every plugin setting.
 *
 * @return array
 */
function sch_get_default_settings() {
	return array(
		'recipient_email' => get_option( 'admin_email' ),
		'success_message' => __( 'Thank you! Your message has been sent successfully.', 'smart-contact-hub' ),
		'whatsapp_number' => '',
		'enable_whatsapp' => 'no',
		'form_title'      => __( 'Get in Touch', 'smart-contact-hub' ),
	);
}


/**
 * Saved settings merged over the defaults.
 *
 * @return array
 */
function sch_get_settings() {
	$saved = get_option( SCH_OPTION_NAME, array() );


	return wp_parse_args( is_array( $saved ) ? $saved : array(), sch_get_default_settings() );
}


/**
 * Stores default settings on first activation without overwriting existing ones.
 */
function sch_activate() {
	if ( false === get_option( SCH_OPTION_NAME ) ) {
		add_option( SCH_OPTION_NAME, sch_get_default_settings() );
	}
}
register_activation_hook( __FILE__, 'sch_activate' );


/**
 * Boots the admin and frontend modules.
 */
function sch_init() {
	SCH_Admin::init();
	SCH_Frontend::init();
}
add_action( 'plugins_loaded', 'sch_init' );

