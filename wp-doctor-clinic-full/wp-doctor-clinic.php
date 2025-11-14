<?php
/*
Plugin Name: WP Doctor Clinic
Description: نوبت‌دهی مطب - نسخه نمونه عملیاتی (شامل منوی مدیریت، AJAX، تقویم شمسی ساده)
Version: 1.0.0
Author: saman balahang
Text Domain: wp-doctor-clinic
Domain Path: /languages
*/

if ( ! defined( 'ABSPATH' ) ) exit;

define('WDC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WDC_PLUGIN_URL', plugin_dir_url(__FILE__));

// Includes
require_once WDC_PLUGIN_DIR . 'includes/class-installer.php';
require_once WDC_PLUGIN_DIR . 'includes/class-assets.php';
require_once WDC_PLUGIN_DIR . 'includes/class-admin-menu.php';
require_once WDC_PLUGIN_DIR . 'includes/class-shortcodes.php';
require_once WDC_PLUGIN_DIR . 'includes/helpers.php';

// Activation / Deactivation
register_activation_hook(__FILE__, array('WDC_Installer', 'activate'));
register_deactivation_hook(__FILE__, array('WDC_Installer', 'deactivate'));

// Initialize
add_action('plugins_loaded', function(){
    WDC_Assets::init();
    WDC_Admin_Menu::init();
    WDC_Shortcodes::init();
});
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'wdc_add_settings_link');

function wdc_add_settings_link($links) {
    $settings_link = '<a href="admin.php?page=wdc-clinic">تنظیمات</a>';
    array_unshift($links, $settings_link);
    return $links;
}
