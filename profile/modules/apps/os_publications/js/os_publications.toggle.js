/**
 * @file
 * Toggle customizations for OpenScholar.
 */

(function ($, Drupal) {
  "use strict";

  Drupal.behaviors.publicationsToggle = {
    attach: function (context, settings) {
      // Toggle Abstract field body.
      let abstractField = '.field--abstract';
      $(abstractField, context).on('click', function (e) {
        $(this).toggleClass('active');
        $(this).siblings('.abstract--content').toggleClass('visually-hidden');
      });
    }
  };

})(jQuery, Drupal);
