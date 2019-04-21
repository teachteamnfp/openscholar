/**
 * Pairs a link and a block of content. The link will toggle the appearance of
 * that block content
 */

(function ($, Drupal) {
    Drupal.behaviors.osToggle = {
      attach: function (context, settings) {
          $('.toggle', context).once('.toggle').each(function() {
            $(this, context).click(function(event) {
              event.preventDefault();
              let potentials = $(this).parent().siblings('.os-slider');
              if (potentials.length) {
                $(potentials).toggleClass("visually-hidden");
              }
            });
          });
      }
    };

    Drupal.behaviors.osReminder = {
      attach: function (context, settings) {
          const $remindercheckbox = $('.rng-event-settings input[name="field_send_reminder_checkbox[value]"]');
          const $field = $('.rng-event-settings .field--name-field-send-reminder');
          $(window).bind("load", function() {
            if ($remindercheckbox.is(':checked')) {
              $field.removeClass('visually-hidden');
            }
          });
          $remindercheckbox.on('change', function() {
          if ($field.length) {
            if ($(this).is(':checked')) {
              $field.removeClass('visually-hidden');
              $field.show();
            }
            else {
              $field.hide();
            }
          }
        });
      }
    };
})(jQuery, Drupal);