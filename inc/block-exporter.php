<?php
/**
 * Handles the block exporting features
 * @package MailPoet to Blocks Converter
 */
class MailPoet_to_Blocks_Converter_Exporter {

	public function __construct() {

		$this->plugin_name = 'mailpoet-to-blocks';
		$this->version     = '0.0.1';

	}

	public function get_single_newsletter() {

		$builder = new MailPoet_to_Blocks_Converter_Builder();
		$single_newsletter = $builder->get_single_newsletter(67);

		return $single_newsletter;

	}


	public function create_newsletter_post() {

		$post = $this->get_single_newsletter();

		

		// TODO: insert $post["id"] to track already inserted
		// TODO: use $post["status"] to create status of posts. sent=publish
		// TODO: use $post["status"] to create status of posts. sent=publish
		// TODO: figure out post author $post["sender_address"], $post["sender_name"], $post["reply_to_address"], $post["reply_to_name"]
		// TODO: use $post["preheader"] to create meta for pre-header
		$post_array = array(
		// 	'post_type'    => ,
			'post_title'    => $post["subject"],
		// 	'post_content'  => ,
		// 	'post_status'   => 'publish',
		// 	'post_author'   => ,
		// 	'post_category' => ,
			'post_date'     => $post["sent_at"],
		// 	'tags_input'    => '',
		// 	'tax_input'     => '',
		// 	'meta_input'    => '',
		);

		// Insert the post into the database
		wp_insert_post( $post_array );

	}

	public function get_newsletter_title() {

	}

}
new MailPoet_to_Blocks_Converter_Exporter();
