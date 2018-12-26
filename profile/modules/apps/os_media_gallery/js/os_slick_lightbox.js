(function ($, Drupal) {

    'use strict';

    Drupal.behaviors.osSlickLightbox = {
        attach: function (context) {
            $('.slick--optionset--slick-media-gallery .slick-track').slickLightbox({
                caption: 'caption'
            });
        }
    };

}(jQuery, Drupal));
