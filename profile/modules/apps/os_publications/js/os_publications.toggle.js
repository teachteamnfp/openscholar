/**
 * @file
 * Toggle customizations for OpenScholar.
 */

(function ($, Drupal) {
    "use strict";

    Drupal.behaviors.publicationsToggle = {
        attach: function (context, settings) {
            // Toggle Abstract field body.
            let abstractField = '.field--name-bibcite-abst-e';
            $(abstractField + ' .field--label').on('click' , function(e){
                $(this).siblings('.field--item').toggleClass('visually-hidden');
            });
        }
    };

})(jQuery, Drupal);
