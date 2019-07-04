/**
 * @file
 * Performs alterations in contextual links for listing pages.
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

  Drupal.behaviors.vsiteContextual = {
    attach: function () {
      $(document).once().bind('drupalContextualLinkAdded', function (event, data) {
        // Makes sure that after node delete via contextual link, the user is
        // redirected to it's listing.
        let $deleteOption = data.$el.find('li.entitynodedelete-form');

        if ($deleteOption.length) {
          let $link = $deleteOption.find('a');
          let url = new URL($link.attr('href'), window.location.origin);
          let currentPath = window.location.pathname;

          url.searchParams.set('destination', currentPath);

          $link.attr('href', decodeURIComponent(url.toString()));
        }

        // Make sure that node edit via contextual link, the user is redirected
        // to correct location.

        let $editOption = data.$el.find('li.entitynodeedit-form');
        if ($editOption.length) {
          let $link = $editOption.find('a');
          let url = new URL($link.attr('href'), window.location.origin);
          let currentPath = window.location.pathname;

          url.searchParams.set('destination', currentPath);

          $link.attr('href', decodeURIComponent(url.toString()));
        }
      });
    },
  };
})(jQuery, Drupal, drupalSettings);
