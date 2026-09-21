/**
 * Slider Meta - Multi Media Uploader
 * پشتیبانی از سه فیلد تصویر (دسکتاپ / تبلت / موبایل)
 */
(function ($) {
    'use strict';

    $(function () {
        const $box = $('.gpds-slider-box');
        if (!$box.length) return;

        let frame = null;
        let $activeField = null;

        // یک فریم مشترک، هدفش بر اساس فیلد فعال تغییر می‌کنه
        function getFrame() {
            if (frame) return frame;

            frame = wp.media({
                title:    'انتخاب بنر اسلایدر',
                button:   { text: 'استفاده از این تصویر' },
                library:  { type: 'image' },
                multiple: false,
            });

            frame.on('select', function () {
                const img = frame.state().get('selection').first().toJSON();
                if (!$activeField) return;

                $activeField.find('.gpds-slider-box__input').val(img.id);
                $activeField.find('.gpds-slider-box__preview')
                    .html('<img src="' + img.url + '" alt="">')
                    .show();
                $activeField.find('.gpds-slider-box__remove').show();
                $activeField.find('.gpds-slider-box__upload').text('تغییر');
            });

            return frame;
        }

        // آپلود
        $box.on('click', '.gpds-slider-box__upload', function (e) {
            e.preventDefault();
            $activeField = $(this).closest('.gpds-slider-box__field');
            getFrame().open();
        });

        // حذف
        $box.on('click', '.gpds-slider-box__remove', function (e) {
            e.preventDefault();
            const $field = $(this).closest('.gpds-slider-box__field');

            $field.find('.gpds-slider-box__input').val('');
            $field.find('.gpds-slider-box__preview').hide().empty();
            $field.find('.gpds-slider-box__remove').hide();
            $field.find('.gpds-slider-box__upload').text('انتخاب');
        });
    });

})(jQuery);