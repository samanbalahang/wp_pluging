<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WDC_Assets {
    public static function init() {
        add_action('wp_enqueue_scripts', array(__CLASS__, 'public_assets'));
        add_action('admin_enqueue_scripts', array(__CLASS__, 'admin_assets'));
        add_action('wp_ajax_wdc_get_times', array(__CLASS__, 'ajax_get_times'));
        add_action('wp_ajax_nopriv_wdc_get_times', array(__CLASS__, 'ajax_get_times'));
        add_action('wp_ajax_wdc_book', array(__CLASS__, 'ajax_book'));
        add_action('wp_ajax_nopriv_wdc_book', array(__CLASS__, 'ajax_book'));
    }

    public static function public_assets() {
        wp_enqueue_style('wdc-public-style', WDC_PLUGIN_URL . 'public/css/public-style.css');
        wp_enqueue_script('wdc-public-js', WDC_PLUGIN_URL . 'public/js/booking.js', array('jquery'), null, true);
        wp_localize_script('wdc-public-js', 'WDC_Ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wdc_nonce')
        ));
    }

    public static function admin_assets($hook) {
        wp_enqueue_style('wdc-admin-style', WDC_PLUGIN_URL . 'admin/css/admin-style.css');
        wp_enqueue_script('wdc-admin-js', WDC_PLUGIN_URL . 'admin/js/admin.js', array('jquery'), null, true);
    }

    // AJAX: return available times for a date (expects 'date' param in Y-m-d)
    public static function ajax_get_times() {
        check_ajax_referer('wdc_nonce', 'nonce');
        if ( empty($_POST['date']) ) {
            wp_send_json_error('تاریخ ارسال نشده است');
        }
        $date = sanitize_text_field($_POST['date']);
        $timestamp = strtotime($date);
        if ( $timestamp === false ) wp_send_json_error('تاریخ نامعتبر');

        $weekday = (int) date('w', $timestamp); // 0 (Sunday) - 6 (Saturday)
        global $wpdb;
        $schedules_table = $wpdb->prefix . 'wdc_schedules';
        $appointments_table = $wpdb->prefix . 'wdc_appointments';

        $schedules = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$schedules_table} WHERE weekday=%d", $weekday));

        if ( empty($schedules) ) {
            wp_send_json_success(array('times'=>array()));
        }

        $times = array();
        foreach ($schedules as $s) {
            $start = strtotime($s->start_time);
            $end = strtotime($s->end_time);
            $interval = intval($s->interval_minutes);
            for ($t = $start; $t < $end; $t += $interval*60) {
                $time_str = date('H:i:s', $t);
                // check if slot exists
                $exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$appointments_table} WHERE appointment_date=%s AND appointment_time=%s", $date, $time_str));
                if ( intval($exists) === 0 ) {
                    $times[] = $time_str;
                }
            }
        }
        wp_send_json_success(array('times'=>$times));
    }

    // AJAX: book appointment
    public static function ajax_book() {
        check_ajax_referer('wdc_nonce', 'nonce');
        $data = array_map('sanitize_text_field', $_POST);
        $required = array('date','time','name','mobile');
        foreach ($required as $r) {
            if ( empty($data[$r]) ) wp_send_json_error('فیلد ' . $r . ' لازم است');
        }
        $date = $data['date'];
        $time = $data['time'];
        $name = $data['name'];
        $mobile = $data['mobile'];

        global $wpdb;
        $appointments_table = $wpdb->prefix . 'wdc_appointments';
        // check unique slot
        $exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$appointments_table} WHERE appointment_date=%s AND appointment_time=%s", $date, $time));
        if ( intval($exists) > 0 ) {
            wp_send_json_error('این ساعت قبلاً رزرو شده است');
        }

        // find or create user by mobile (user_login = mobile)
        $user = get_user_by('login', $mobile);
        if ( ! $user ) {
            $password = wp_generate_password(8, false);
            $user_id = wp_create_user($mobile, $password, $mobile . '@example.invalid');
            if ( is_wp_error($user_id) ) {
                $user_id = null;
            } else {
                wp_update_user(array('ID'=>$user_id, 'display_name'=>$name));
                update_user_meta($user_id, 'phone', $mobile);
            }
        } else {
            $user_id = $user->ID;
        }

        $insert = $wpdb->insert($appointments_table, array(
            'user_id' => $user_id,
            'patient_name' => $name,
            'patient_mobile' => $mobile,
            'appointment_date' => $date,
            'appointment_time' => $time,
            'status' => 'confirmed'
        ), array('%d','%s','%s','%s','%s','%s'));

        if ( $insert === false ) {
            wp_send_json_error('خطا در ذخیره‌سازی نوبت');
        }

        wp_send_json_success('رزرو با موفقیت انجام شد');
    }
}
