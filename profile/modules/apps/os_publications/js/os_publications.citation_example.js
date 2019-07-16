/**
 * @file
 * Fullcalendar customizations for OpenScholar.
 */

(function ($, Drupal) {
  "use strict";

  Drupal.behaviors.publications = {
    attach: function (context, settings) {

      let $formClass = $('.form-item-os-publications-preferred-bibliographic-format');

      $formClass.mouseover(function () {
        let checkbox = $(this).find('input').val();
        let default_style = drupalSettings.default_style;
        $("#" + default_style).hide();

        let $checkboxElement = $('#' + checkbox);
        $checkboxElement.removeClass('hidden');
        $checkboxElement.show();
      });
      $formClass.mouseout(function () {
        let checkbox = $(this).find('input').val();
        $("#" + checkbox).hide();
      });
      $("#edit-os-publications-preferred-bibliographic-format").mouseout(function () {
        let default_style = drupalSettings.default_style;
        $("#" + default_style).show();
      });

      // Select All option for publication types.
      let $selectAll = $('#edit-os-publications-filter-publication-types #edit-os-publications-filter-publication-types-all');
      let $fieldWrapper = $('#edit-os-publications-filter-publication-types');
      $selectAll.on('click', function () {
        $fieldWrapper.find('input').each(function () {
          if ($selectAll.prop('checked')) {
            $(this).prop('checked', true);
          }
          else {
            $(this).prop('checked', false);
          }
        });
      });
    }
  };

})(jQuery, Drupal);
