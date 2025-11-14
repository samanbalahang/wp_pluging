<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WDC_Installer {
    public static function activate() {
        global $wpdb;
        $charset = $wpdb->get_charset_collate();

        $schedules_table = $wpdb->prefix . 'wdc_schedules';
        $appointments_table = $wpdb->prefix . 'wdc_appointments';

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        $sql = "CREATE TABLE {$schedules_table} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            weekday tinyint(1) NOT NULL, -- 0 (Sunday) - 6 (Saturday)  (WP: Sunday=0)
            start_time time NOT NULL,
            end_time time NOT NULL,
            interval_minutes smallint(3) NOT NULL DEFAULT 30,
            PRIMARY KEY  (id)
        ) {$charset};";
        dbDelta($sql);

        $sql2 = "CREATE TABLE {$appointments_table} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NULL,
            patient_name varchar(191) NOT NULL,
            patient_mobile varchar(50) NOT NULL,
            appointment_date date NOT NULL, -- stored in Gregorian Y-m-d
            appointment_time time NOT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            status varchar(20) NOT NULL DEFAULT 'pending',
            PRIMARY KEY  (id),
            UNIQUE KEY unique_slot (appointment_date, appointment_time)
        ) {$charset};";
        dbDelta($sql2);
    }

    public static function deactivate() {
        // No destructive actions on deactivate
    }
}
