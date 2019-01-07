/**
 * @file
 * Fullcalendar customizations for OpenScholar.
 */

(function ($, Drupal) {
  "use strict";

  Drupal.fullcalendar.plugins.os_fullcalendar = {
    options: function (fullcalendar, settings) {
      if (!settings.os_fullcalendar) {
        return;
      }

      return $.extend({
        views: {
          listUpcoming: {
            type: 'list',
            visibleRange: function (currentDate) {
              return {
                start: currentDate.clone().add(1, 'days'),
                end: currentDate.clone().add(2, 'weeks'),
              }
            },
            buttonText: Drupal.t('Upcoming Events'),
          }
        }
      }, settings.os_fullcalendar);
    }
  };

})(jQuery, Drupal);
