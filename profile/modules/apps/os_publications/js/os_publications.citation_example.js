/**
 * @file
 * Fullcalendar customizations for OpenScholar.
 */

(function ($, Drupal) {
  "use strict";

  Drupal.behaviors.publications = {
    attach: function (context, settings) {

      var formClass = '.form-item-os-publications-preferred-bibliographic-format';

      $(formClass).mouseover(function () {
        var checkbox = $(this).find('input').val();
        var default_style = drupalSettings.default_style;
        $("#" + default_style).hide();
        $("#" + checkbox).removeClass('hidden');
        $("#" + checkbox).show();
      });
      $(formClass).mouseout(function () {
        var checkbox = $(this).find('input').val();
        var default_style = drupalSettings.default_style;
          $("#" + checkbox).hide();
      });
      $("#edit-os-publications-preferred-bibliographic-format").mouseout(function () {
        var default_style = drupalSettings.default_style;
        $("#" + default_style).show();
      });
    }
  };

})(jQuery, Drupal);
