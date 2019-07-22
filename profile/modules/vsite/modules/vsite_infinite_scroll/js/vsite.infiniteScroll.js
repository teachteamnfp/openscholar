/**
 * @file
 * Performs alterations on infinite scroll view on publications page.
 */

(function ($, Drupal) {
    /*
     * Hide redundant row headers.
     */
    function osPublicationsHideCategoryHeaders() {
       let $selectors = $('.view-publications h3');

        $selectors.each(function() {
            let html = $(this).html();
            $selectors.filter(function() {
                return $(this).text() === html;
            }).not(':first').remove();
        });
    }

    /**
     * Initializes the alterations.
     */
    function init() {
        osPublicationsHideCategoryHeaders();
    }

    Drupal.behaviors.vsiteInfiniteScroll = {
        attach: function () {
            init();
        },
    };
})(jQuery, Drupal);
