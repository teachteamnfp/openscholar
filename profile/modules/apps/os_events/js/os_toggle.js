/**
 * Pairs a link and a block of content. The link will toggle the appearance of
 * that block content
 */

(function ($, Drupal) {
    Drupal.behaviors.osToggle = {
      attach: function (context, settings) {
          $('.toggle', context).once('.toggle').each(function() {
            $(this, context).click(function(event) {
              event.preventDefault();
              let potentials = $(this).parent().siblings('.os-slider');
              if (potentials.length) {
                $(potentials).toggleClass("visually-hidden");
              }
            });
          });
      }
    };
})(jQuery, Drupal);