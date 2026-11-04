<?php
/**
 * Admin settings page under Settings > Smart Contact Hub.
 *
 * @package SmartContactHub
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class SCH_Admin {


	const PAGE_SLUG    = 'smart-contact-hub';
	const OPTION_GROUP = 'sch_settings_group';
	const SECTION_ID   = 'sch_general_section';


	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_menu_page' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( SCH_PLUGIN_FILE ), array( __CLASS__, 'add_action_links' ) );
	}


	public static function add_menu_page() {
		add_options_page(
			__( 'Smart Contact Hub Settings', 'smart-contact-hub' ),
			__( 'Smart Contact Hub', 'smart-contact-hub' ),
			'manage_options',
			self::PAGE_SLUG,
			array( __CLASS__, 'render_page' )
		);
	}


	/**
	 * Adds a "Settings" link on the Plugins screen.
	 *
	 * @param array $links Existing action links.
	 * @return array
	 */
	public static function add_action_links( $links ) {
		$settings_link = sprintf(
			'<a href="%1$s">%2$s</a>',
			esc_url( admin_url( 'options-general.php?page=' . self::PAGE_SLUG ) ),
			esc_html__( 'Settings', 'smart-contact-hub' )
		);


		array_unshift( $links, $settings_link );


		return $links;
	}


	public static function register_settings() {
		register_setting(
			self::OPTION_GROUP,
			SCH_OPTION_NAME,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize_settings' ),
				'default'           => sch_get_default_settings(),
			)
		);


		add_settings_section(
			self::SECTION_ID,
			__( 'Form Configuration', 'smart-contact-hub' ),
			array( __CLASS__, 'render_section_intro' ),
			self::PAGE_SLUG
		);


		$fields = array(
			'recipient_email' => array(
				'label'       => __( 'Recipient Email', 'smart-contact-hub' ),
				'type'        => 'email',
				'description' => __( 'Where new contact form messages are sent. Defaults to the site admin email.', 'smart-contact-hub' ),
			),
			'success_message' => array(
				'label'       => __( 'Success Message', 'smart-contact-hub' ),
				'type'        => 'text',
				'description' => __( 'Shown to the visitor after their message has been sent.', 'smart-contact-hub' ),