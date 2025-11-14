<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WDC_Shortcodes {
    public static function init() {
        add_shortcode('doctor_booking', array(__CLASS__, 'render_booking'));
    }

    public static function render_booking($atts = array()) {
        ob_start();
        include WDC_PLUGIN_DIR . 'public/views/booking-form.php';
        return ob_get_clean();
    }
}
