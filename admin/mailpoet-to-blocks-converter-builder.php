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
					"SELECT count(ID) as total FROM {$wpdb->prefix}mailpoet_newsletters WHERE type='standard'"
				) 
			);
			$count = $count[0]->total;
			return $count;
		}
	}

	/**
	 * Get all IDs of newsletters from the database
	 *
	 * @return array $ids array of all IDs in database
	 */
	public function get_mailpoet_newsletters_ids() {
		global $wpdb;
		if ( true === $this->check_mailpoet_newsletters_table() ) {
			$ids = $wpdb->get_results( 
				$wpdb->prepare(
					"SELECT id FROM {$wpdb->prefix}mailpoet_newsletters ORDER BY id"
				) 
			);

			$id_array = array();

			foreach ( $ids as $id => $key ) {
				$id_array[] = $key->id;
			}

			return $id_array;
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
	 * @param integer $newsletter_id a specific newsletter to pull
	 * @return array $newsletter_blocks an array of the content of a newsletter body
	 */
	public function get_newsletter_blocks( int $post_id = 0 ) {
		$newsletter = $this->get_single_newsletter( $post_id );
		$newsletter_body = json_decode( $newsletter->body, true );
		if ( empty ( $newsletter_body ) ) {
			return;
		}
		$newsletter_blocks = $newsletter_body['content'];
		return $newsletter_blocks;
	}

	/**
	 * Extract the body of a newsletter to manipulate
	 * @param integer $newsletter_id a specific newsletter to pull
	 * @return array $newsletter_blocks an array of the content of a newsletter body
	 */
	public function get_newsletter_body( int $post_id = 0 ) {
		$newsletter_blocks = $this->get_newsletter_blocks( $post_id );
		if ( !isset ( $newsletter_blocks ) || null === $newsletter_blocks ) {
			return;
		}
		$newsletter_body = array ( $this->newsletter_blocks_loop( $newsletter_blocks ) );
		return $newsletter_body;
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
		$text = $block['text'];

		// Removes line breaks and empty paragraph tags
		// Also removes WP block comments that are usually out of place for what we need
		$strip_text = array(
			'\n',
			'<!-- wp:paragraph -->',
			'<!-- /wp:paragraph -->',
			'<!-- wp:image -->',
			'<!-- /wp:image -->',
			'<!-- wp:quote -->',
			'<!-- /wp:quote -->',
			'<p></p>',
			'<p> </p>',
			'<p style="text-align: left"></p>',
		);
		$text = str_replace( $strip_text, '', $text );

		// Re-add paragraph and quote WP comments along with linebreaks
		$text = str_replace( '<p', "\r\n<!-- wp:paragraph -->\r\n<p", $text );
		$text = str_replace( '</p>', "</p>\r\n<!-- /wp:paragraph -->\r\n", $text );
		$text = str_replace( '<!-- wp:paragraph --></p>', '</p>', $text );
		$text = str_replace( '<blockquote', "\r\n<!-- wp:quote -->\r\n<blockquote", $text );
		$text = str_replace( '</blockquote>', "</blockquote>\r\n<!-- /wp:quote -->\r\n", $text );

		$html = '';
		$html .= $text;

		// Add content to the build_content global
		global $build_content;
		$build_content .= $html;
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
		// TODO: inserting width into <img> tag breaks the block, so we're ignoring for now
		$image_width     = isset( $block['width'] ) ? $block['width'] : '';
		// height is not set by MailPoet, so we'll be using auto for height value
		$image_height    = isset( $block['height'] ) ? $block['height'] : '';
		$image_align     = isset( $block['styles']['block']['textAlign'] ) ? $block['styles']['block']['textAlign'] : 'center';

		$html = '';
		
		if ( ! empty ( $image_link ) && false === $image_fullWidth ) {
			$html .= "\r\n";
			$html .= '<!-- wp:image {"align":"' . $image_align . '","width":' . $image_width . ',"height":' . $image_height . ',"linkDestination":"custom"} -->';
			$html .= "\r\n";
			$html .= '<figure class="wp-block-image align' . $image_align . '">';
			$html .= '<a href="' . $image_link . '">';
			// $html .= '<img src="' . $image_src . '" alt="' . $image_alt . '" width="' . $image_width . '"/>';
			$html .= '<img src="' . $image_src . '" alt="' . $image_alt . '" />';
			$html .= '</a>';
			$html .= '</figure>';
			$html .= "\r\n";
			$html .= '<!-- /wp:image -->';
			$html .= "\r\n";
		}
		if ( empty ( $image_link ) && true === $image_fullWidth ) {
			$html .= "\r\n";
			$html .= '<!-- wp:image {"align":"full","width":' . $image_width . ',"height":' . $image_height . ',"linkDestination":"none"} -->';
			$html .= "\r\n";
			$html .= '<figure class="wp-block-image alignfull">';
			// $html .= '<img src="' . $image_src . '" alt="' . $image_alt . '" width="' . $image_width . '"/>';
			$html .= '<img src="' . $image_src . '" alt="' . $image_alt . '" />';
			$html .= '</figure>';
			$html .= "\r\n";
			$html .= '<!-- /wp:image -->';
			$html .= "\r\n";
		}
		if ( ! empty ( $image_link ) && true === $image_fullWidth ) {
			$html .= "\r\n";
			$html .= '<!-- wp:image {"align":"full","width":' . $image_width . ',"height":' . $image_height . ',"linkDestination":"custom"} -->';
			$html .= "\r\n";
			$html .= '<figure class="wp-block-image alignfull">';
			$html .= '<a href="' . $image_link . '">';
			// $html .= '<img src="' . $image_src . '" alt="' . $image_alt . '" width="' . $image_width . '"/>';
			$html .= '<img src="' . $image_src . '" alt="' . $image_alt . '" />';
			$html .= '</a>';
			$html .= '</figure>';
			$html .= "\r\n";
			$html .= '<!-- /wp:image -->';
			$html .= "\r\n";
		}
		if ( empty ( $image_link ) && false === $image_fullWidth ) {
			$html .= "\r\n";
			$html .= '<!-- wp:image {"align":"' . $image_align . '","width":' . $image_width . ',"height":' . $image_height . ',"linkDestination":"none"} -->';
			$html .= "\r\n";
			$html .= '<figure class="wp-block-image align' . $image_align . '">';
			// $html .= '<img src="' . $image_src . '" alt="' . $image_alt . '" width="' . $image_width . '"/>';
			$html .= '<img src="' . $image_src . '" alt="' . $image_alt . '" />';
			$html .= '</figure>';
			$html .= "\r\n";
			$html .= '<!-- /wp:image -->';
			$html .= "\r\n";
		}

		// Add content to the build_content global
		global $build_content;
		$build_content .= $html;
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
		$html .= "\r\n";
		$html .= '<!-- wp:spacer {"height":"' . $spacer_height . '"} -->';
		$html .= "\r\n";
		$html .= '<div style="height:' . $spacer_height . '" aria-hidden="true" class="wp-block-spacer"></div>';
		$html .= "\r\n";
		$html .= '<!-- /wp:spacer -->';
		$html .= "\r\n";

		// Add content to the build_content global
		global $build_content;
		$build_content .= $html;
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
			$html .= "\r\n";
			$html .= '<!-- wp:separator {"style":{"color":{"background":"' . $divider_backgroundColor . '"}},"className":"is-style-dots"} -->';
			$html .= "\r\n";
			$html .= '<hr class="wp-block-separator has-text-color has-alpha-channel-opacity has-background is-style-dots" style="background-color:' . $divider_backgroundColor . ';color:' . $divider_backgroundColor . '"/>';
			$html .= "\r\n";
			$html .= '<!-- /wp:separator -->';
			$html .= "\r\n";
		} else {
			$html .= "\r\n";
			$html .= '<!-- wp:separator {"opacity":"css"}, {"style":{"color":{"background":"' . $divider_backgroundColor . '"}} -->';
			$html .= "\r\n";
			$html .= '<hr class="wp-block-separator has-css-opacity style="background-color:' . $divider_backgroundColor . ';color:' . $divider_backgroundColor . '"/>';
			$html .= "\r\n";
			$html .= '<!-- /wp:separator -->';
			$html .= "\r\n";
		}

		// Add content to the build_content global
		global $build_content;
		$build_content .= $html;
	}

	/**
	 * Converts a footer block array to a pargraph block type
	 *
	 * @param array $block the content and styling of the block
	 * @return string $html
	 */
	public function create_block_footer( $block ) {
		$html = $block['text'];
		// Currently none of this is getting used
		$footer_backgroundColor = isset( $block['styles']['block']['backgroundColor'] ) ? $block['styles']['block']['backgroundColor'] : 'transparent';
		$footer_fontColor       = isset( $block['styles']['text']['fontColor'] ) ? $block['styles']['text']['fontColor'] : 'inherit';
		$footer_fontSize        = isset( $block['styles']['text']['fontSize'] ) ? $block['styles']['text']['fontSize'] : 'inherit';
		$footer_textAlign       = isset( $block['styles']['text']['textAlign'] ) ? $block['styles']['text']['textAlign'] : 'left';
		$footer_linkColor       = isset( $block['styles']['link']['fontColor'] ) ? $block['styles']['link']['fontColor'] : 'inherit';
		$footer_textDecoration  = isset( $block['styles']['link']['textDecoration'] ) ? $block['styles']['link']['textDecoration'] : 'inherit';

		// Add content to the build_content global
		global $build_content;
		$build_content .= $html;
	}

	/**
	 * Gets the post type that is set to convert newsletters into
	 *
	 * @return string $post_type The post type to convert to
	 */
	public function get_newsletter_post_type() {
		$options = get_option('mailpoet_to_blocks_settings');
		$post_type = $options['convert_post_type'];
		return $post_type;
	}

	/**
	 * Gets the ID of the newsletter author
	 *
	 * @param string $sender_address The email address of the newsletter sender
	 * @return int $author_id The ID of the author of the newsletter or current user
	 */
	public function get_single_newsletter_author( string $sender_address = '' ) {
		$author = get_user_by( 'email', $sender_address );

		if ( false === $author || ! isset ( $author ) ) {
			$author_id = get_current_user_id();
		} else {
			$author_id = $author->ID;
		}

		return $author_id;
	}

	/**
	 * Convert MailPoet status to WordPress post status
	 *
	 * @param string $status The status of the newsletter object
	 * @return string $newsletter_status The updated status to match WP post object
	 */
	public function get_newsletter_status( $status = '' ) {
		if ( null === $status ) {
			wp_die();
		} elseif ( 'sent' === $status ) {
			$newsletter_status = 'publish';
		} elseif ( 'scheduled' === $status ) {
			$newsletter_status = 'future';
		} elseif ( 'draft' === $status ) {
			$newsletter_status = 'draft';
		}
		return $newsletter_status;
	}

	/**
	 * Inserts newsletter preheader from MailPoet as a paragraph at the top of the newsletter
	 *
	 * @param string $preheader the text of the preheader
	 */
	public function insert_newsletter_preheader( string $preheader = '' ) {

		// exit early if there is no preheader text
		if ( null === $preheader ) {
			return;
		}

		$html = "\r\n<!-- wp:paragraph -->\r\n<p>";
		$html .= $preheader;
		$html .= "</p>\r\n<!-- /wp:paragraph -->\r\n";

		// Add content to the build_content global
		global $build_content;
		$build_content .= $html;
	}

	/**
	 * Creates a post from newsletter data and inserts it into the WP database
	 *
	 * @param integer $post_id the ID of the newsletter being inserted
	 */
	public function create_newsletter_post( int $post_id = 0 ) {

		global $build_content;

		$newsletter = $this->get_single_newsletter( $post_id );

		// Ensure that we're actually working with a newsletter
		if ( null == $newsletter->id || 0 === $post_id ) {
			return 'No newsletter ID given.';
			exit;
		}

		// Modify newsletter details for insertion
		$newsletter_post_type = $this->get_newsletter_post_type();
		$newsletter_preheader = $this->insert_newsletter_preheader( $newsletter->preheader );
		$newsletter_content   = $this->get_newsletter_body( $newsletter->id );
		$newsletter_status    = $this->get_newsletter_status( $newsletter->status );
		$newsletter_author    = $this->get_single_newsletter_author( $newsletter->sender_address );
		$newsletter_type      = $newsletter->type;

		// Only insert standard type newsletters into new posts
		if ( 'standard' !== $newsletter_type ) {
			return;
		}

		// TODO: insert $post["id"] to track already inserted
		$post_array = array(
			'post_type'    => $newsletter_post_type,
			'post_title'    => $newsletter->subject,
			'post_content'  => $build_content,
			'post_status'   => $newsletter_status,
			'post_author'   => $newsletter_author,
			'post_date'     => $newsletter->sent_at,
			'meta_input'    => array(
				'newsletter_preheader' => $newsletter->preheader,
			),
		);

		// Insert the post into the database
		wp_insert_post( $post_array );

	}

	/**
	 * Loops all newsletters to create posts
	 *
	 * @param integer $post_id the ID of the newsletter being inserted
	 */
	public function create_newsletter_posts() {
		// Get the global of newsletter content to wipe it after insert
		global $build_content;

		// Get each newsletter by ID
		$ids = $this->get_mailpoet_newsletters_ids();

		// loop through all newsletters by ID to create a post
		foreach ( $ids as $id ) {
			$this->create_newsletter_post( $id );
			// wipes newslettr content global
			$build_content = '';
		}
	}

}
new MailPoet_to_Blocks_Converter_Builder();
