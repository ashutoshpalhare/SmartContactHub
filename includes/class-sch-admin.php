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

	/**
	 * Register admin hooks.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_menu_page' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
		add_filter(
			'plugin_action_links_' . plugin_basename( SCH_PLUGIN_FILE ),
			array( __CLASS__, 'add_action_links' )
		);
	}

	/**
	 * Add the settings screen.
	 */
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
	 * Add a shortcut to the plugin settings from the Plugins screen.
	 *
	 * @param array $links Existing action links.
	 * @return array
	 */
	public static function add_action_links( $links ) {
		$url = admin_url( 'options-general.php?page=' . self::PAGE_SLUG );
		array_unshift(
			$links,
			sprintf(
				'<a href="%1$s">%2$s</a>',
				esc_url( $url ),
				esc_html__( 'Settings', 'smart-contact-hub' )
			)
		);

		return $links;
	}

	/**
	 * Register settings and their fields.
	 */
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
				'description' => __( 'Where new contact form messages are delivered. Defaults to the site admin email.', 'smart-contact-hub' ),
			),
			'form_title' => array(
				'label'       => __( 'Form Title', 'smart-contact-hub' ),
				'type'        => 'text',
				'description' => __( 'Heading displayed above the public contact form.', 'smart-contact-hub' ),
			),
			'success_message' => array(
				'label'       => __( 'Success Message', 'smart-contact-hub' ),
				'type'        => 'textarea',
				'description' => __( 'Confirmation shown after a message has been delivered.', 'smart-contact-hub' ),
			),
			'whatsapp_number' => array(
				'label'       => __( 'WhatsApp Number', 'smart-contact-hub' ),
				'type'        => 'text',
				'description' => __( 'Country code and number using digits only, for example 919876543210.', 'smart-contact-hub' ),
			),
			'enable_whatsapp' => array(
				'label'       => __( 'Enable WhatsApp Button', 'smart-contact-hub' ),
				'type'        => 'checkbox',
				'description' => __( 'Offer visitors a WhatsApp follow-up link after successful email delivery.', 'smart-contact-hub' ),
			),
		);

		foreach ( $fields as $key => $field ) {
			add_settings_field(
				'sch_' . $key,
				$field['label'],
				array( __CLASS__, 'render_field' ),
				self::PAGE_SLUG,
				self::SECTION_ID,
				array(
					'label_for'   => 'sch-' . $key,
					'field'       => $key,
					'type'        => $field['type'],
					'description' => $field['description'],
				)
			);
		}
	}

	/**
	 * Explain the settings section.
	 */
	public static function render_section_intro() {
		echo '<p>' . esc_html__( 'Choose where messages are delivered and customize the visitor-facing confirmation and follow-up options.', 'smart-contact-hub' ) . '</p>';
	}

	/**
	 * Render a single settings field.
	 *
	 * @param array $args Field configuration.
	 */
	public static function render_field( $args ) {
		$settings = sch_get_settings();
		$field    = $args['field'];
		$value    = isset( $settings[ $field ] ) ? $settings[ $field ] : '';

		if ( 'checkbox' === $args['type'] ) {
			printf(
				'<label for="%1$s"><input id="%1$s" name="%2$s[%3$s]" type="checkbox" value="yes" %4$s> %5$s</label>',
				esc_attr( $args['label_for'] ),
				esc_attr( SCH_OPTION_NAME ),
				esc_attr( $field ),
				checked( 'yes', $value, false ),
				esc_html__( 'Show the WhatsApp follow-up button', 'smart-contact-hub' )
			);
		} elseif ( 'textarea' === $args['type'] ) {
			printf(
				'<textarea id="%1$s" name="%2$s[%3$s]" class="large-text" rows="3" maxlength="240">%4$s</textarea>',
				esc_attr( $args['label_for'] ),
				esc_attr( SCH_OPTION_NAME ),
				esc_attr( $field ),
				esc_textarea( $value )
			);
		} else {
			printf(
				'<input id="%1$s" name="%2$s[%3$s]" type="%4$s" class="regular-text" value="%5$s" maxlength="%6$s">',
				esc_attr( $args['label_for'] ),
				esc_attr( SCH_OPTION_NAME ),
				esc_attr( $field ),
				esc_attr( $args['type'] ),
				esc_attr( $value ),
				'recipient_email' === $field ? '190' : '80'
			);
		}

		if ( ! empty( $args['description'] ) ) {
			printf( '<p class="description">%s</p>', esc_html( $args['description'] ) );
		}
	}

	/**
	 * Sanitize the submitted settings.
	 *
	 * @param mixed $input Raw settings.
	 * @return array
	 */
	public static function sanitize_settings( $input ) {
		$defaults = sch_get_default_settings();

		if ( ! is_array( $input ) ) {
			return $defaults;
		}

		$recipient = isset( $input['recipient_email'] ) && is_string( $input['recipient_email'] )
			? sanitize_email( $input['recipient_email'] )
			: $defaults['recipient_email'];
		$title = isset( $input['form_title'] ) && is_string( $input['form_title'] )
			? sanitize_text_field( $input['form_title'] )
			: $defaults['form_title'];
		$message = isset( $input['success_message'] ) && is_string( $input['success_message'] )
			? sanitize_textarea_field( $input['success_message'] )
			: $defaults['success_message'];
		$number = isset( $input['whatsapp_number'] ) && is_string( $input['whatsapp_number'] )
			? preg_replace( '/\D+/', '', sanitize_text_field( $input['whatsapp_number'] ) )
			: '';

		if ( ! is_email( $recipient ) || SCH_Compat::text_length( $recipient ) > 190 ) {
			add_settings_error( SCH_OPTION_NAME, 'sch_invalid_recipient', __( 'Enter a valid recipient email address (maximum 190 characters).', 'smart-contact-hub' ) );
			$recipient = $defaults['recipient_email'];
		}

		$title = SCH_Compat::text_substr( trim( $title ), 0, 80 );
		if ( '' === $title ) {
			$title = $defaults['form_title'];
		}

		$message = SCH_Compat::text_substr( trim( $message ), 0, 240 );
		if ( '' === $message ) {
			$message = $defaults['success_message'];
		}

		if ( '' !== $number && ( SCH_Compat::text_length( $number ) < 8 || SCH_Compat::text_length( $number ) > 15 ) ) {
			add_settings_error( SCH_OPTION_NAME, 'sch_invalid_whatsapp', __( 'WhatsApp numbers must contain 8 to 15 digits, including the country code.', 'smart-contact-hub' ) );
			$number = '';
		}

		$enabled = isset( $input['enable_whatsapp'] ) && 'yes' === $input['enable_whatsapp'] ? 'yes' : 'no';
		if ( 'yes' === $enabled && '' === $number ) {
			add_settings_error( SCH_OPTION_NAME, 'sch_whatsapp_missing', __( 'Add a valid WhatsApp number before enabling the WhatsApp button.', 'smart-contact-hub' ) );
			$enabled = 'no';
		}

		return array(
			'recipient_email' => $recipient,
			'form_title'      => $title,
			'success_message' => $message,
			'whatsapp_number' => $number,
			'enable_whatsapp' => $enabled,
		);
	}

	/**
	 * Render the settings screen.
	 */
	public static function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Smart Contact Hub', 'smart-contact-hub' ); ?></h1>
			<?php settings_errors(); ?>
			<form action="options.php" method="post">
				<?php
				settings_fields( self::OPTION_GROUP );
				do_settings_sections( self::PAGE_SLUG );
				submit_button( __( 'Save Settings', 'smart-contact-hub' ) );
				?>
			</form>
		</div>
		<?php
	}
}
