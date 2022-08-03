<?php
/**
 * Handles admin functions for the plugin
 * @package MailPoet to Blocks Converter
 */
class MailPoet_to_Blocks_Converter_Admin {

	public function __construct() {

		$this->plugin_name = 'mailpoet-to-blocks';
		$this->version     = '0.0.1';

		add_action( 'admin_menu', array( $this, 'setup_plugin_options_menu' ), 9 );
		add_action( 'admin_init', array( $this, 'initialize_general_settings' ) );

	}

	/**
	 * Creates main settings menu page, as well as submenu page
	 */
	public function setup_plugin_options_menu() {

		add_submenu_page(
			'tools.php',
			__( 'MailPoet to Blocks Converter', 'mailpoet-to-blocks' ),
			__( 'MailPoet to Blocks', 'mailpoet-to-blocks' ),
			'manage_options',
			'mailpoet_to_blocks_settings',
			array( $this, 'render_settings_page_content' )
		);

	}

	/**
	 * Renders a simple page to display for the theme menu defined above.
	 */
	public function render_settings_page_content() {
		?>
		<!-- Create a header in the default WordPress 'wrap' container -->
		<div class="wrap">

			<h2><?php esc_attr_e( 'MailPoet to Block Converter', 'mailpoet-to-blocks' ); ?></h2>
			<?php
			// Display any settings errors registered to the settings_error hook
			settings_errors();

			?>

			<p>
				<?php esc_html_e( 'This process will look for newsletters created with MailPoet, then allow you to convert them to block posts of a post type of your choosing.', 'mailpoet-to-blocks'); ?>
			</p>
			<p>
				<?php esc_html_e( 'Please note that not all MailPoet content types are supported at this time.', 'mailpoet-to-blocks'); ?>
			</p>
			<p>
				<?php
				$newsletter_count = get_mailpoet_newsletters_count();
				printf( __( 'You have <strong>%d</strong> newsletters in MailPoet, ready to convert.', 'mailpoet-to-blocks' ), $newsletter_count  );
				?>
			</p>

			<form method="post" action="tools.php">
				<?php

				settings_fields( 'mailpoet_to_blocks_general_settings' );
				do_settings_sections( 'mailpoet_to_blocks_general_settings' );

				submit_button();

				?>
			</form>
			<?php display_results(); ?>

		</div><!-- /.wrap -->
		<?php
	}

	/**
	 * Initializes the general settings by registering the Sections, Fields, and Settings.
	 *
	 * This function is registered with the 'admin_init' hook.
	 */
	public function initialize_general_settings() {

		add_settings_section(
			'general_settings_section',
			__( 'General Settings', 'mailpoet-to-blocks' ),
			'',
			'mailpoet_to_blocks_general_settings'
		);

		add_settings_field(
			'termstext',
			__( 'Text', 'mailpoet-to-blocks' ),
			array( $this, 'text_input_callback' ),
			'mailpoet_to_blocks_general_settings',
			'general_settings_section',
			array(
				'label_for'    => 'termstext',
				'option_group' => 'mailpoet_to_blocks_settings',
				'option_id'    => 'termstext',
			)
		);

		register_setting(
			'mailpoet_to_blocks_general_settings',
			'mailpoet_to_blocks_settings'
		);

	}

	/**
	 * Input Callbacks
	 */
	public function text_input_callback( $text_input ) {

		// Get arguments from setting
		$option_group = $text_input['option_group'];
		$option_id    = $text_input['option_id'];
		$option_name  = "{$option_group}[{$option_id}]";

		// Get existing option from database
		$options      = get_option( $option_group );
		$option_value = isset( $options[ $option_id ] ) ? $options[ $option_id ] : '0';

		// Render the output
		echo "<input type='text' id='{$option_id}' name='{$option_name}' value='{$option_value}' />";

	}

	public function checkbox_input_callback( $checkbox_input ) {

		// Get arguments from setting
		$option_group       = $checkbox_input['option_group'];
		$option_id          = $checkbox_input['option_id'];
		$option_name        = "{$option_group}[{$option_id}]";
		$option_description = $checkbox_input['option_description'];

		// Get existing option from database
		$options      = get_option( $option_group );
		$option_value = isset( $options[ $option_id ] ) ? $options[ $option_id ] : '';

		// Render the output
		$input  = '';
		$input .= "<input type='checkbox' id='{$option_id}' name='{$option_name}' value='1' " . checked( $option_value, 1, false ) . ' />';
		$input .= "<label for='{$option_id}'>{$option_description}</label>";

		echo $input;

	}

	// Validate inputs
	public function validate_inputs( $input ) {
		// Create our array for storing the validated options
		$output = array();
		// Loop through each of the incoming options
		foreach ( $input as $key => $value ) {
			// Check to see if the current option has a value. If so, process it.
			if ( isset( $input[ $key ] ) ) {
				// Strip all HTML and PHP tags and properly handle quoted strings
				$output[ $key ] = wp_strip_all_tags( stripslashes( $input[ $key ] ) );
			}
		} // end foreach
		// Return the array processing any additional functions filtered by this action
		return apply_filters( 'validate_inputs', $output, $input );
	}


}
new MailPoet_to_Blocks_Converter_Admin();
