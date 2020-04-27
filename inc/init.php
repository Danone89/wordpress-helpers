<?php

use Wordpress_helpers\classes\pods\Pods_Helper;

remove_filter('the_content', 'wpautop');
add_action('plugins_loaded', function () {
    Datatables::init(self::$plugin_dir);
    if (function_exists('notification_whitelabel')) {
        notification_whitelabel();
        $uploads_dir = wp_upload_dir();
        notification_sync($uploads_dir['basedir'] . '/alerts');
    }
});

add_action('rest_api_init', function () {
    WP_Form_Processing::hook_rest();
});
add_action('wp', function () {
    WP_Form_Processing::hook();
    do_action('pixinit');
});
/**
 * Load wordpress api library
 */
add_action('wp_enqueue_scripts', function () {
    wp_register_script('jquery-repeatable', self::$plugin_dir . 'assets/jquery.repeatable.js', ['jquery'], 1.1, true);
    wp_register_script('jquery-mask-input', self::$plugin_dir . 'assets/jquery.mask.min.js', ['jquery'], 1.1, true);
}, 5);

/**
 * register pods shortcode
 */
if (class_exists('Pods')) {
    add_action('wp', function () {
        Pods_Helper::register_form_shortcode();
        Pods_Helper::forms_add_fields_class('form-control');
        Pods_Helper::register_form_view_loader();
    });
}
if (defined('ZABBIX_IP')) {
    add_filter('rest_authentication_errors', function ($result) {
        if (defined('ZABBIX_IP') && $_SERVER['REMOTE_ADDR'] === ZABBIX_IP) {
            return true;
        }
        return $result;
    }, PHP_INT_MAX);
}
