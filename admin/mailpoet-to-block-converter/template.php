<?php
/**
 * Template for MailPoet to Block Converter
 */

?>
<div class="wrap">
	<h1>
		<?php esc_html_e( 'MailPoet to Block Converter', 'mailpoet-to-blocks'); ?>
	</h1>
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

	<?php
	display_results();
	?>
</div>
