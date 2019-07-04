/**
 * @file
 * Performs alterations in contextual links for listing pages.
 */

(function ($, Drupal) {
  /**
   * Registers to event `drupalContextualLinkAdded`.
   */
  function registerDrupalContextualLinkAddedEvent() {
    $(document).once().bind('drupalContextualLinkAdded', function (event, data) {
      let $editOption = data.$el.find('li.entitynodeedit-form');
      let $deleteOption = data.$el.find('li.entitynodedelete-form');

      [$editOption, $deleteOption].forEach(function ($element) {
        if ($element.length) {
          alterDestination($element);
        }
      });
    });
  }

  /**
   * Makes sure that after edit/delete user is redirected to listing.
   *
   * @param $el
   *   The contextual link element.
   */
  function alterDestination($el) {
    let $link = $el.find('a');
    let url = new URL($link.attr('href'), window.location.origin);
    let currentPath = window.location.pathname;

    url.searchParams.set('destination', currentPath);

    $link.attr('href', decodeURIComponent(url.toString()));
  }

  /**
   * Initializes the alterations.
   */
  function init() {
    registerDrupalContextualLinkAddedEvent();
  }

  Drupal.behaviors.vsiteContextual = {
    attach: function () {
      init();
    },
  };
})(jQuery, Drupal);
