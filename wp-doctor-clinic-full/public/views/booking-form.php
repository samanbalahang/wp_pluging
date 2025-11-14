<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$days = WDC_helpers::next_days_with_jalali(30);
?>
<div id="wdc-booking">
    <h3>رزرو نوبت</h3>
    <p>لطفاً یک روز را انتخاب کنید (تاریخ به شمسی نمایش داده شده)</p>
    <div id="wdc-days">
        <?php foreach($days as $d): ?>
            <button class="wdc-day" data-date="<?php echo esc_attr($d['gregorian']); ?>"><?php echo esc_html($d['jalali'] . ' - ' . $d['label']); ?></button>
        <?php endforeach; ?>
    </div>
    <div id="wdc-times" style="margin-top:16px;"></div>
    <form id="wdc-book-form" style="margin-top:12px;display:none;">
        <input type="hidden" name="date" id="wdc_date">
        <input type="hidden" name="time" id="wdc_time">
        <p><label>نام و نام خانوادگی<br><input type="text" name="name" id="wdc_name" required></label></p>
        <p><label>موبایل<br><input type="text" name="mobile" id="wdc_mobile" required></label></p>
        <p><button type="submit">رزرو</button></p>
    </form>
    <div id="wdc-message"></div>
</div>
