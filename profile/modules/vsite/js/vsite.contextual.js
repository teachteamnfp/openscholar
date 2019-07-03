/**
 * @file
 * Performs alterations in contextual links.
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
      // Makes sure that after node delete via contextual link, the user is
      // redirected to it's listing.
      $(document).once().bind('drupalContextualLinkAdded', function (event, data) {
        let $deleteOption = data.$el.find('li.entitynodedelete-form');

        if ($deleteOption.length) {
          let $link = $deleteOption.find('a');
          let url = new URL($link.attr('href'), window.location.origin);
          let newDestination = drupalSettings.spaces.url + redirectMapping[drupalSettings.vsite.nodeBundle];

          url.searchParams.set('destination', newDestination);

          $link.attr('href', decodeURIComponent(url.toString()));
        }
      });
    },
  };
})(jQuery, Drupal, drupalSettings);
