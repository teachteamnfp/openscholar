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
        eventRender: function (event, element) {
          if (element.hasClass('fc-event-future') && !element.hasClass('fc-day-grid-event')) {
            let date = new Date(event['start']['_i']);
            let offset = date.getTimezoneOffset() * 60000;
            let dateInMs = date.valueOf();
            let eventDate = (dateInMs + offset)/1000;
            let nid = event.eid;
            element.html(drupalSettings['os_events']['node'][nid]);
            element.find('#events_signup_modal_form').attr('href', '/events/signup/' + nid + '/' + eventDate);
          }
          else if (element.hasClass('fc-event-past') && !element.hasClass('fc-day-grid-event')) {
            let nid = event.eid;
            element.html(drupalSettings[nid]);
          }
        },
        eventAfterAllRender: function (view) {
          if (view.name == 'listUpcoming' || view.name == 'listPast') {
            let tableSubHeaders = $(".fc-list-heading");
            tableSubHeaders.each(function () {
              $(this).nextUntil(".fc-list-heading").wrapAll("<tr class='fc-list-item-parent'></tr>");
            });
            //wrapping events date, moth, year to seperate span for ui
            $('.fc-list-heading-main').each(function () {
              let eventdata = $(this).text().split(' ');
              $(this).empty();
              $(this).append($("<span class='event-year'>").text(eventdata[0]));
              $(this).append($("<span class='event-start-month'>").text(eventdata[1]));
              $(this).append($("<span class='event-start-day'>").text(eventdata[2]));
            });
            //wrapping every 2 tr(event date and event title, location) to single tr so that UI doesn't break in small screen
            var elems = $(".fc-listUpcoming-view tbody > tr, .fc-listPast-view tbody > tr");
            var wrapper = $('<tr class="fc-wrapper" />');
            var pArrLen = elems.length;
            for (var i = 0; i < pArrLen; i += 2) {
              elems.filter(':eq(' + i + '),:eq(' + (i + 1) + ')').wrapAll(wrapper);
            };
          }
          //wrapping content in td for ui
          if (view.name == 'listWeek' || view.name == 'listDay') {
            $('.fc-event-future').each(function(){
              $(this).wrapInner('<td>');
            });
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
            listDayFormat: 'YYYY MMM DD',
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
            listDayFormat: 'YYYY MMM DD',
          },
        },
        'buttonText': {
          listWeek: Drupal.t('Week'),
          listDay: Drupal.t('Day'),
        },
      }, settings.os_fullcalendar);
    }
  };

  /**
   * Alters modal title.
   *
   * The title is displayed as plain text. It is enforced to be rendered as HTML
   * here.
   */
  function showModalEventRegisterHandler() {
    $('#drupal-modal').once().on('show.bs.modal', function () {
      let $modalTitleElement = $(this).find('.modal-title');
      let eventUrl = $(this).find('.modal-body article').attr('about');
      let modalTitleText = $modalTitleElement.text();

      $modalTitleElement.html('<a href="' + eventUrl + '">' + modalTitleText + '</a>');
    });
  }

  Drupal.behaviors.events = {
    attach: function (context, settings) {

      const $multicheck = $('#edit-field-singup-multiple-wrapper');
      const $checkbox = $('.form-item-field-recurring-date-0-rrule .form-textarea-wrapper');
      const $message = $('#event-change-notify');
      $(window).bind("load", function() {
        if (!$checkbox.find('input').is(':checked')) {
          $multicheck.hide();
        }
      });
      $checkbox.find('input').on('change', function () {
        if ($(this).is(':checked')) {
          $message.removeClass('visually-hidden');
          $message.show();
          $message.appendTo($(this).parent());
          $multicheck.show();
        }
        else {
          $message.hide();
          $multicheck.hide();
        }
      });

      showModalEventRegisterHandler();
    }
  };

})(jQuery, Drupal);
