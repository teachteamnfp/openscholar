/**
 * @file
 * Performs alterations in contextual links for full-view pages.
 */

(function ($, Drupal, drupalSettings) {
  const redirectMapping = {
    blog: 'blog',
    events: 'calendar',
    class: 'classes',
    link: 'links',
    news: 'news',
    person: 'people',
    presentation: 'presentations',
    software_project: 'software',
  };

  /**
   * Makes sure that after delete user is redirected to listing.
   *
   * @param $el
   *   The delete contextual link element.
   */
  function alterDeleteDestination($el) {
    let $link = $el.find('a');
    let url = new URL($link.attr('href'), window.location.origin);
    let newDestination = drupalSettings.spaces.url + redirectMapping[drupalSettings.vsite.nodeBundle];

    url.searchParams.set('destination', newDestination);

    $link.attr('href', decodeURIComponent(url.toString()));
  }

  /**
   * Makes sure that after edit user is redirected to node full-view.
   *
   * @param $el
   *   The edit contextual link element.
   */
  function alterEditDestination($el) {
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

  /**
   * Registers to event `drupalContextualLinkAdded`.
   */
  function registerDrupalContextualLinkAddedEvent() {
    $(document).once().bind('drupalContextualLinkAdded', function (event, data) {
      let $deleteOption = data.$el.find('li.entitynodedelete-form');

      if ($deleteOption.length) {
        alterDeleteDestination($deleteOption);
      }

      let $editOption = data.$el.find('li.entitynodeedit-form');

      if ($editOption.length) {
        alterEditDestination($editOption);
      }
    });
  }

  Drupal.behaviors.vsiteContextual = {
    attach: function () {
      init();
    },
  };
})(jQuery, Drupal, drupalSettings);
