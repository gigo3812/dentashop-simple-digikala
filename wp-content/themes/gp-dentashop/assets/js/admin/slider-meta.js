/**
 * Slider Meta - Media Uploader
 */
(function ($) {
    'use strict';

    $(function () {
        const $box = $('.gpds-slider-box');
        if (!$box.length) return;

        const $input   = $box.find('#gpds_slider_banner_id');
        const $preview = $box.find('.gpds-slider-box__preview');
        const $upload  = $box.find('.gpds-slider-box__upload');
        const $remove  = $box.find('.gpds-slider-box__remove');

        let frame = null;

        $upload.on('click', function (e) {
            e.preventDefault();

            if (!frame) {
                frame = wp.media({
                    title:    'انتخاب بنر اسلایدر',
                    button:   { text: 'استفاده از این تصویر' },
                    library:  { type: 'image' },
                    multiple: false,
                });

                frame.on('select', function () {
                    const img = frame.state().get('selection').first().toJSON();
                    $input.val(img.id);
                    $preview.html('<img src="' + img.url + '" alt="">').show();
                    $remove.show();
                    $upload.text('تغییر بنر');
                });
            }

            frame.open();
        });

        $remove.on('click', function (e) {
            e.preventDefault();
            $input.val('');
            $preview.hide().empty();
            $remove.hide();
            $upload.text('انتخاب بنر');
        });
    });

})(jQuery);