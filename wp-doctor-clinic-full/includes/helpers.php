<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WDC_helpers {
    // Convert Gregorian Y-m-d to Jalali yyyy/mm/dd
    public static function gregorian_to_jalali($g_date) {
        // expects Y-m-d
        $parts = explode('-', $g_date);
        if (count($parts) < 3) return $g_date;
        $gy = intval($parts[0]);
        $gm = intval($parts[1]);
        $gd = intval($parts[2]);
        list($jy,$jm,$jd) = self::gregorian_to_jalali_calc($gy,$gm,$gd);
        return sprintf('%04d/%02d/%02d', $jy,$jm,$jd);
    }

    // using standard algorithm
    public static function gregorian_to_jalali_calc($g_y,$g_m,$g_d) {
        $g_d_m = array(0,31,28,31,30,31,30,31,31,30,31,30,31);
        $j_months = array(0,31,31,31,31,31,31,30,30,30,30,30,29);
        $gy = $g_y-1600;
        $gm = $g_m-1;
        $gd = $g_d-1;
        $g_day_no = 365*$gy + intval(($gy+3)/4) - intval(($gy+99)/100) + intval(($gy+399)/400);
        for ($i=0;$i<$gm;$i++) $g_day_no += $g_d_m[$i+1];
        $g_day_no += $gd;

        $j_day_no = $g_day_no - 79;

        $j_np = intval($j_day_no / 12053);
        $j_day_no = $j_day_no % 12053;

        $jy = 979 + 33*$j_np + 4*intval($j_day_no/1461);
        $j_day_no %= 1461;

        if ($j_day_no >= 366) {
            $jy += intval(($j_day_no-1)/365);
            $j_day_no = ($j_day_no-1)%365;
        }

        for ($i = 0; $i < 11 && $j_day_no >= $j_months[$i+1]; ++$i) {
            $j_day_no -= $j_months[$i+1];
        }
        $jm = $i+1;
        $jd = $j_day_no+1;

        return array($jy,$jm,$jd);
    }

    // create next N days list in gregorian with jalali labels
    public static function next_days_with_jalali($n=30) {
        $out = array();
        for ($i=0;$i<$n;$i++) {
            $ts = strtotime('+' . $i . ' day');
            $g = date('Y-m-d', $ts);
            $j = self::gregorian_to_jalali($g);
            $out[] = array('gregorian'=>$g,'jalali'=>$j,'label'=>self::weekday_label(date('w',$ts)));
        }
        return $out;
    }

    public static function weekday_label($w) {
        $labels = array(0=>'یکشنبه',1=>'دوشنبه',2=>'سه‌شنبه',3=>'چهارشنبه',4=>'پنج‌شنبه',5=>'جمعه',6=>'شنبه');
        return isset($labels[$w]) ? $labels[$w] : $w;
    }
}
