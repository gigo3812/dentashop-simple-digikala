/**
 * Banner Meta - Multi Media Uploader
 * پشتیبانی از سه فیلد تصویر (دسکتاپ / تبلت / موبایل)
 */
(function ($) {
    'use strict';

    $(function () {
        const $box = $('.gpds-banner-box');
        if (!$box.length) return;

        let frame = null;
        let $activeField = null;

        // یک فریم مشترک، هدفش بر اساس فیلد فعال تغییر می‌کنه
        function getFrame() {
            if (frame) return frame;

            frame = wp.media({
                title:    'انتخاب تصویر بنر',
                button:   { text: 'استفاده از این تصویر' },
                library:  { type: 'image' },
                multiple: false,
            });

            frame.on('select', function () {
                const img = frame.state().get('selection').first().toJSON();
                if (!$activeField) return;

                $activeField.find('.gpds-banner-box__input').val(img.id);
                $activeField.find('.gpds-banner-box__preview')
                    .html('<img src="' + img.url + '" alt="">')
                    .show();
                $activeField.find('.gpds-banner-box__remove').show();
                $activeField.find('.gpds-banner-box__upload').text('تغییر تصویر');
            });

            return frame;
        }

        // آپلود
        $box.on('click', '.gpds-banner-box__upload', function (e) {
            e.preventDefault();
            $activeField = $(this).closest('.gpds-banner-box__field');
            getFrame().open();
        });

        // حذف
        $box.on('click', '.gpds-banner-box__remove', function (e) {
            e.preventDefault();
            const $field = $(this).closest('.gpds-banner-box__field');

            $field.find('.gpds-banner-box__input').val('');
            $field.find('.gpds-banner-box__preview').hide().empty();
            $field.find('.gpds-banner-box__remove').hide();
            $field.find('.gpds-banner-box__upload').text('انتخاب تصویر');
        });
    });

})(jQuery);