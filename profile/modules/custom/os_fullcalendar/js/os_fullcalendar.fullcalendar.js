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
        eventRender: function(event, element) {
          if (element.hasClass('fc-event-future') && !element.hasClass('fc-day-grid-event')) {
            let nid = event.eid;
            element.html(drupalSettings[nid]);
          }
          else if (element.hasClass('fc-event-past') && !element.hasClass('fc-day-grid-event')) {
            let nid = event.eid;
            element.html(drupalSettings[nid]);
          }
        },
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

  Drupal.behaviors.events = {
    attach: function (context, settings) {

      const $checkbox = $('.form-item-field-recurring-date-0-rrule .form-textarea-wrapper');
      const $message = $('#event-change-notify');
      $checkbox.find('input').on('change', function () {
        if ($(this).is(':checked')) {
          $message.removeClass('visually-hidden');
          $message.show();
          $message.appendTo($(this).parent());
        }
        else {
          $message.hide();
        }
      });
    }
  };

})(jQuery, Drupal);

