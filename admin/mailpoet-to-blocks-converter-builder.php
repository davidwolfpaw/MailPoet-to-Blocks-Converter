<?php
/**
 * Handles builder functions for the plugin
 * @package MailPoet to Blocks Converter
 */
class MailPoet_to_Blocks_Converter_Builder {

	// Global variable to build a post from blocks
	protected $build_content;

	public function __construct() {

		$this->plugin_name = 'mailpoet-to-blocks';
		$this->version     = '0.0.1';

		$this->build_content = $build_content;

		// add_action( 'admin_menu', array( $this, 'setup_plugin_options_menu' ), 9 );
		// add_action( 'admin_init', array( $this, 'initialize_general_settings' ) );

	}

	/**
	 * A test function to show me some output for debugging
	 *
	 * @return void
	 */
	public function display_results() {
		global $build_content;
		$results = '';
		$results = $this->get_newsletter_body();
		var_dump( $build_content );
	}

	/**
	 * Confirm that the MailPoet newsletters table exists
	 * 
	 * TODO: error handling on table not existing
	 */
	public function check_mailpoet_newsletters_table() {
		global $wpdb;
		$table_name = $wpdb->prefix . "mailpoet_newsletters";
		if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name)) === $table_name ) {
			return true;
		} else {
			printf('Table does not exist' . ': ' . print_r($table_name, true));
		}
	}

	/**
	 * Get the number of newsletters that exist in the database
	 *
	 * @return int $count total newsletter rows that exist in the newsletters table
	 */
	public function get_mailpoet_newsletters_count() {
		global $wpdb;
		if ( true === $this->check_mailpoet_newsletters_table() ) {
			$count = $wpdb->get_results( 
				$wpdb->prepare(
					"SELECT count(ID) as total FROM {$wpdb->prefix}mailpoet_newsletters"
				) 
			);
			$count = $count[0]->total;
			return $count;
		}
	}

	/**
	 * Pull a single newsletter from the database
	 *
	 * @param integer $newsletter_id a specific newsletter to pull
	 * @return object $newsletter a single newsletter object
	 */
	public function get_single_newsletter( int $newsletter_id = 0 ) {
		global $wpdb;
		if ( true === $this->check_mailpoet_newsletters_table() ) {
			$newsletter = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}mailpoet_newsletters WHERE id = %d", $newsletter_id));
			return $newsletter;
		}
	}

	/**
	 * Get the blocks of content from the object json
	 * TODO: write functions to iterate over newsletter rows
	 *
	 * @return array $newsletter_blocks an array of the content of a newsletter body
	 */
	public function get_newsletter_blocks() {
		$newsletter = $this->get_single_newsletter( 67 );
		$newsletter_body = json_decode( $newsletter->body, true );
		$newsletter_blocks = $newsletter_body['content'];
		return $newsletter_blocks;
	}

	/**
	 * Extract the body of a newsletter to manipulate
	 */
	public function get_newsletter_body() {
		$newsletter_blocks = $this->get_newsletter_blocks();
		$newsletter_body = $this->newsletter_blocks_loop( $newsletter_blocks );
	}

	/**
	 * Loop through the body of the newsletter to extract array values
	 *
	 * @param array $array the array to loop through
	 * @param integer $depth the depth of the multidimensional array
	 * @return void
	 */
	public function newsletter_blocks_loop( array $array, int $depth = 0 ) {
		// Loop each row of array
		foreach ( $array as $key => $value ) {
			// If we've got a blocks array, transform it!
			if ( is_array( $value ) && 'blocks' === $key ) {
				foreach ( $value as $block ) {
					$this->transform_block( $block );
				}
			}
			// Recurse through until a blocks array is found or no arrays are left
			if ( is_array( $value ) && 'blocks' !== $key ) {
				$this->newsletter_blocks_loop( $value, $depth + 1 );
			}
		}
	}

	/**
	 * Sorts the arrays to send to the proper function to create blocks
	 * TODO: Make blocks from containers to wrap other blocks
	 *
	 * @param array $block
	 * @return void
	 */
	public function transform_block( $block ) {
		if ( "container" === $block["type"] ) {
			$this->newsletter_blocks_loop( $block );
		}
		if ( "text" === $block["type"] ) {
			$this->create_block_text( $block );
		}
		if ( "image" === $block["type"] ) {
			$this->create_block_image( $block );
		}
		if ( "spacer" === $block["type"] ) {
			$this->create_block_spacer( $block );
		}
		if ( "divider" === $block["type"] ) {
			$this->create_block_separator( $block );
		}
		if ( "footer" === $block["type"] ) {
			$this->create_block_footer( $block );
		}
	}

	/**
	 * Converts a text block array to a pargraph block type
	 *
	 * @param array $block the content and styling of the block
	 * @return string $html
	 */
	public function create_block_text( $block ) {
		$html = $block['text'];

		global $build_content;
		$build_content .= $html;

		return $html;
	}

	/**
	 * Converts an image block array to an image block type
	 *
	 * @param array $block the content and styling of the block
	 * @return string $html
	 */
	public function create_block_image( $block ) {
		$image_link      = isset( $block['link'] ) ? $block['link'] : '';
		$image_src       = isset( $block['src'] ) ? $block['src'] : '';
		$image_alt       = isset( $block['alt'] ) ? $block['alt'] : '';
		$image_fullWidth = isset( $block['fullWidth'] ) ? $block['fullWidth'] : false;
		$image_width     = isset( $block['width'] ) ? $block['width'] : '';
		// height is not set by MailPoet, so we'll be using auto for height value
		$image_height    = isset( $block['height'] ) ? $block['height'] : '';
		$image_align     = isset( $block['styles']['block']['textAlign'] ) ? $block['styles']['block']['textAlign'] : 'center';

		$html = '';
		
		if ( ! empty ( $image_link ) && false === $image_fullWidth ) {
			$html .= '<!-- wp:image {"align":"' . $image_align . '","width":' . $image_width . ',"height":' . $image_height . ',"linkDestination":"custom"} -->';
			$html .= '<figure class="wp-block-image align' . $image_align . '">';
			$html .= '<a href="' . $image_link . '">';
			$html .= '<img src="' . $image_src . '" alt="' . $image_alt . '" width="' . $image_width . '"/>';
			$html .= '</a>';
			$html .= '</figure>';
			$html .= '<!-- /wp:image -->';
		}
		if ( empty ( $image_link ) && true === $image_fullWidth ) {
			$html .= '<!-- wp:image {"align":"full","width":' . $image_width . ',"height":' . $image_height . ',"linkDestination":"none"} -->';
			$html .= '<figure class="wp-block-image alignfull">';
			$html .= '<img src="' . $image_src . '" alt="' . $image_alt . '" width="' . $image_width . '"/>';
			$html .= '</figure>';
			$html .= '<!-- /wp:image -->';
		}
		if ( ! empty ( $image_link ) && true === $image_fullWidth ) {
			$html .= '<!-- wp:image {"align":"full","width":' . $image_width . ',"height":' . $image_height . ',"linkDestination":"custom"} -->';
			$html .= '<figure class="wp-block-image alignfull">';
			$html .= '<a href="' . $image_link . '">';
			$html .= '<img src="' . $image_src . '" alt="' . $image_alt . '" width="' . $image_width . '"/>';
			$html .= '</a>';
			$html .= '</figure>';
			$html .= '<!-- /wp:image -->';
		}
		if ( empty ( $image_link ) && false === $image_fullWidth ) {
			$html .= '<!-- wp:image {"align":"' . $image_align . '","width":' . $image_width . ',"height":' . $image_height . ',"linkDestination":"none"} -->';
			$html .= '<figure class="wp-block-image align' . $image_align . '">';
			$html .= '<img src="' . $image_src . '" alt="' . $image_alt . '" width="' . $image_width . '"/>';
			$html .= '</figure>';
			$html .= '<!-- /wp:image -->';
		}

		global $build_content;
		$build_content .= $html;

		return $html;
	}

	/**
	 * Converts a spacer block array to a spacer block type
	 *
	 * @param array $block the content and styling of the block
	 * @return string $html
	 */
	public function create_block_spacer( $block ) {
		$spacer_height = isset( $block['styles']['block']['height'] ) ? $block['styles']['block']['height'] : '30px';

		$html = '';
		$html .= '<!-- wp:spacer {"height":"' . $spacer_height . '"} -->';
		$html .= '<div style="height:' . $spacer_height . '" aria-hidden="true" class="wp-block-spacer"></div>';
		$html .= '<!-- /wp:spacer -->';

		global $build_content;
		$build_content .= $html;

		return $html;
	}

	/**
	 * Converts a divider block array to a separator block type
	 *
	 * @param array $block the content and styling of the block
	 * @return string $html
	 */
	public function create_block_separator( $block ) {
		$divider_backgroundColor = isset( $block['styles']['block']['backgroundColor'] ) ? $block['styles']['block']['backgroundColor'] : 'transparent';
		$divider_borderStyle     = isset( $block['styles']['block']['borderStyle'] ) ? $block['styles']['block']['borderStyle'] : 'solid';

		$html = '';
		// only checking for dotted or not style to match block options
		if ( isset( $block['styles']['block']['borderStyle'] ) && 'dotted' === $block['styles']['block']['borderStyle'] ) {
			$html .= '<!-- wp:separator {"style":{"color":{"background":"' . $divider_backgroundColor . '"}},"className":"is-style-dots"} -->';
			$html .= '<hr class="wp-block-separator has-text-color has-alpha-channel-opacity has-background is-style-dots" style="background-color:' . $divider_backgroundColor . ';color:' . $divider_backgroundColor . '"/>';
			$html .= '<!-- /wp:separator -->';
		} else {
			$html .= '<!-- wp:separator {"opacity":"css"}, {"style":{"color":{"background":"' . $divider_backgroundColor . '"}} -->';
			$html .= '<hr class="wp-block-separator has-css-opacity style="background-color:' . $divider_backgroundColor . ';color:' . $divider_backgroundColor . '"/>';
			$html .= '<!-- /wp:separator -->';
		}

		global $build_content;
		$build_content .= $html;
		
		return $html;
	}

	/**
	 * Converts a footer block array to a pargraph block type
	 *
	 * @param array $block the content and styling of the block
	 * @return string $html
	 */
	public function create_block_footer( $block ) {
		$html = $block['text'];
		$footer_backgroundColor = isset( $block['styles']['block']['backgroundColor'] ) ? $block['styles']['block']['backgroundColor'] : 'transparent';
		$footer_fontColor       = isset( $block['styles']['text']['fontColor'] ) ? $block['styles']['text']['fontColor'] : 'inherit';
		$footer_fontSize        = isset( $block['styles']['text']['fontSize'] ) ? $block['styles']['text']['fontSize'] : 'inherit';
		$footer_textAlign       = isset( $block['styles']['text']['textAlign'] ) ? $block['styles']['text']['textAlign'] : 'left';
		$footer_linkColor       = isset( $block['styles']['link']['fontColor'] ) ? $block['styles']['link']['fontColor'] : 'inherit';
		$footer_textDecoration  = isset( $block['styles']['link']['textDecoration'] ) ? $block['styles']['link']['textDecoration'] : 'inherit';

		global $build_content;
		$build_content .= $html;

		return $html;
	}


}
new MailPoet_to_Blocks_Converter_Builder();
