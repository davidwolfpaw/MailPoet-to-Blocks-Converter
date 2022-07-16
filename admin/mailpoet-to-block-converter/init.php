<?php

// //Register assets for MailPoet to Block Converter
// add_action('init', function () {
//     $handle = 'mailpoet-to-block-converter';
//     $assets = include dirname(__FILE__, 3). "/build/admin-page-$handle.asset.php";
//     $dependencies = $assets['dependencies'];
//     $dependencies[] = 'jquery';
//     wp_register_script(
//         $handle,
//         plugins_url("/build/admin-page-$handle.js", dirname(__FILE__, 2)),
//         $dependencies,
//         $assets['version']
//     );
// });

//Enqueue assets for MailPoet to Block Converter on admin page only
add_action('admin_enqueue_scripts', function ($hook) {
    if ('toplevel_page_mailpoet-to-block-converter' != $hook) {
        return;
    }
    wp_enqueue_script('mailpoet-to-block-converter');
});

//Register MailPoet to Block Converter menu page
add_action('admin_menu', function () {
    add_menu_page(
        __('MailPoet to Block Converter', 'mailpoet-to-blocks'),
        __('MailPoet to Block Converter', 'mailpoet-to-blocks'),
        'manage_options',
        'mailpoet-to-block-converter',
        function () {
            include_once dirname(__FILE__) . '/template.php';
        }
    );
});
