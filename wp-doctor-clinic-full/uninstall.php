<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}
global $wpdb;
$wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . "wdc_schedules");
$wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . "wdc_appointments");
