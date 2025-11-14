(function($){
    $(document).ready(function(){
        $('#wdc-days').on('click', '.wdc-day', function(e){
            e.preventDefault();
            $('.wdc-day').removeClass('selected');
            $(this).addClass('selected');
            var date = $(this).data('date');
            $('#wdc_date').val(date);
            $('#wdc-message').text('در حال بارگذاری ساعات...');
            $.post(WDC_Ajax.ajax_url, {action:'wdc_get_times', date: date, nonce: WDC_Ajax.nonce}, function(resp){
                if (resp.success) {
                    var times = resp.data.times;
                    var html = '';
                    if (times.length === 0) html = '<p>برای این روز ساعتی موجود نیست.</p>';
                    else {
                        times.forEach(function(t){
                            html += '<span class="wdc-time" data-time="'+t+'">'+t.substr(0,5)+'</span>';
                        });
                    }
                    $('#wdc-times').html(html);
                    $('#wdc-message').text('');
                    $('#wdc-book-form').hide();
                } else {
                    $('#wdc-message').text(resp.data || 'خطا در دریافت ساعات');
                }
            });
        });

        $('#wdc-times').on('click', '.wdc-time', function(){
            $('.wdc-time').removeClass('selected');
            $(this).addClass('selected');
            var time = $(this).data('time');
            $('#wdc_time').val(time);
            $('#wdc-book-form').show();
        });

        $('#wdc-book-form').on('submit', function(e){
            e.preventDefault();
            var data = {
                action: 'wdc_book',
                nonce: WDC_Ajax.nonce,
                date: $('#wdc_date').val(),
                time: $('#wdc_time').val(),
                name: $('#wdc_name').val(),
                mobile: $('#wdc_mobile').val()
            };
            $('#wdc-message').text('در حال ارسال رزرو...');
            $.post(WDC_Ajax.ajax_url, data, function(resp){
                if (resp.success) {
                    $('#wdc-message').text(resp.data);
                    $('#wdc-book-form').hide();
                    $('#wdc-times').html('');
                } else {
                    $('#wdc-message').text(resp.data || 'خطا در رزرو');
                }
            });
        });
    });
})(jQuery);