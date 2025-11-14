<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WDC_Admin_Menu {
    public static function init() {
        add_action('admin_menu', array(__CLASS__, 'register_menu'));
        add_action('admin_post_wdc_save_schedule', array(__CLASS__, 'save_schedule'));
    }

    public static function register_menu() {
        add_menu_page('Clinic Booking', 'Clinic Booking', 'manage_options', 'wdc-clinic', array(__CLASS__, 'page_dashboard'), 'dashicons-calendar-alt', 6);
        add_submenu_page('wdc-clinic', 'ساعات کاری', 'ساعات کاری', 'manage_options', 'wdc-schedule', array(__CLASS__, 'page_schedule'));
        add_submenu_page('wdc-clinic', 'نوبت‌ها', 'نوبت‌ها', 'manage_options', 'wdc-appointments', array(__CLASS__, 'page_appointments'));
    }

    public static function page_dashboard() {
        echo '<div class="wrap"><h1>Clinic Booking</h1><p>خوش آمدید به سیستم نوبت‌دهی</p></div>';
    }

    public static function page_schedule() {
        global $wpdb;
        $schedules_table = $wpdb->prefix . 'wdc_schedules';
        if ( ! current_user_can('manage_options') ) wp_die('No permission');
        $rows = $wpdb->get_results("SELECT * FROM {$schedules_table} ORDER BY weekday, start_time");
        // group by weekday
        $byday = array();
        foreach ($rows as $r) {
            $byday[$r->weekday][] = $r;
        }
        $weekdays = array(0=>'یکشنبه',1=>'دوشنبه',2=>'سه‌شنبه',3=>'چهارشنبه',4=>'پنج‌شنبه',5=>'جمعه',6=>'شنبه');
        echo '<div class="wrap"><h1>ساعات کاری</h1>';
        echo '<form method="post" action="'.admin_url('admin-post.php').'">';
        echo '<input type="hidden" name="action" value="wdc_save_schedule">';
        wp_nonce_field('wdc_save_schedule');
        echo '<table class="widefat"><thead><tr><th>روز</th><th>ساعات (مثال 09:00 - 13:00)</th><th>هر چند دقیقه</th><th>حذف</th></tr></thead><tbody>';
        for ($d=0;$d<=6;$d++) {
            echo '<tr><td>'.$weekdays[$d].'</td><td>';
            if (!empty($byday[$d])) {
                foreach ($byday[$d] as $r) {
                    echo '<div><input type="text" name="start_'.$r->id.'" value="'.$r->start_time.'"> - <input type="text" name="end_'.$r->id.'" value="'.$r->end_time.'"> Interval: <input type="number" name="interval_'.$r->id.'" value="'.$r->interval_minutes.'"> <label><input type="checkbox" name="delete_ids[]" value="'.$r->id.'"> حذف</label></div>';
                }
            }
            echo '</td><td>--</td><td></td></tr>';
        }
        echo '</tbody></table>';
        echo '<h2>افزودن ساعت جدید</h2>';
        echo '<p>روز: <select name="new_weekday">';
        foreach ($weekdays as $k=>$v) echo '<option value="'.$k.'">'.$v.'</option>';
        echo '</select> Start <input name="new_start" value="09:00"> End <input name="new_end" value="13:00"> Interval <input name="new_interval" value="30"></p>';
        submit_button('ذخیره تغییرات');
        echo '</form></div>';
    }

    public static function save_schedule() {
        if ( ! current_user_can('manage_options') ) wp_die('No permission');
        check_admin_referer('wdc_save_schedule');
        global $wpdb;
        $schedules_table = $wpdb->prefix . 'wdc_schedules';
        // handle deletes
        if ( ! empty($_POST['delete_ids']) && is_array($_POST['delete_ids']) ) {
            foreach ($_POST['delete_ids'] as $id) {
                $wpdb->delete($schedules_table, array('id'=>intval($id)), array('%d'));
            }
        }
        // handle updates for existing rows (fields named like start_ID end_ID interval_ID)
        foreach ($_POST as $k=>$v) {
            if (preg_match('/^start_(\d+)$/', $k, $m)) {
                $id = intval($m[1]);
                $start = sanitize_text_field($v);
                $end = sanitize_text_field($_POST['end_'.$id]);
                $interval = intval($_POST['interval_'.$id]);
                $wpdb->update($schedules_table, array('start_time'=>$start,'end_time'=>$end,'interval_minutes'=>$interval), array('id'=>$id), array('%s','%s','%d'), array('%d'));
            }
        }
        // add new schedule
        if (!empty($_POST['new_weekday']) && !empty($_POST['new_start']) && !empty($_POST['new_end'])) {
            $wpdb->insert($schedules_table, array(
                'weekday' => intval($_POST['new_weekday']),
                'start_time' => sanitize_text_field($_POST['new_start']),
                'end_time' => sanitize_text_field($_POST['new_end']),
                'interval_minutes' => intval($_POST['new_interval'])
            ), array('%d','%s','%s','%d'));
        }
        wp_redirect(admin_url('admin.php?page=wdc-schedule'));
        exit;
    }

    public static function page_appointments() {
        global $wpdb;
        $appointments_table = $wpdb->prefix . 'wdc_appointments';
        $rows = $wpdb->get_results("SELECT * FROM {$appointments_table} ORDER BY appointment_date DESC, appointment_time DESC LIMIT 200");
        echo '<div class="wrap"><h1>نوبت‌ها</h1>';
        echo '<table class="widefat"><thead><tr><th>ID</th><th>نام</th><th>موبایل</th><th>تاریخ (شمسی)</th><th>زمان</th><th>ایجاد</th></tr></thead><tbody>';
        foreach ($rows as $r) {
            $jalali = WDC_helpers::gregorian_to_jalali($r->appointment_date);
            echo '<tr><td>'.$r->id.'</td><td>'.$r->patient_name.'</td><td>'.$r->patient_mobile.'</td><td>'.$jalali.'</td><td>'.$r->appointment_time.'</td><td>'.$r->created_at.'</td></tr>';
        }
        echo '</tbody></table></div>';
    }
}
