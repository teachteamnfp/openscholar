/**
 * @file
 * Performs alterations on infinite scroll view on publications page.
 */

(function ($, Drupal) {
    Drupal.behaviors.vsiteInfiniteScroll = {
        attach: function () {
         // Hide redundant row headers.
          let $selectors = $('.view-publications h3');
          $selectors.each(function() {
            let html = $(this).html();
            $selectors.filter(function() {
              return $(this).text() === html;
            }).not(':first').remove();
          });
        },
    };
})(jQuery, Drupal);
