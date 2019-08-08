(function ($, Drupal) {
  Drupal.behaviors.osSelectAllOption = {
    attach: function (context, settings) {
      $('div.show-select-all-option', context).each(function () {
        $(this).find('input:first').click(function() {
          let isChecked = $(this).prop('checked');
          $(this).parents('.form-checkboxes').find('.form-checkbox').each(function () {
            $(this).prop('checked', isChecked);
          });
        });
      });
    }
  };
})(jQuery, Drupal);
