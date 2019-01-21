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
          },
          listPast: {
            type: 'list',
            visibleRange: function (currentDate) {
              return {
                start: currentDate.clone().subtract(1, 'days'),
                end: currentDate.clone().subtract(2, 'weeks'),
              }
            },
            buttonText: Drupal.t('Past Events'),
          }
        }
      }, settings.os_fullcalendar);
    }
  };

})(jQuery, Drupal);
