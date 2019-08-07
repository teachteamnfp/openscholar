(function ($, Drupal) {
  Drupal.behaviors.theme_picker = {
    attach: function (context, settings) {
      jQuery('.theme-selector').click(function () {
       jQuery('.theme-selector').removeClass('checked');
        if (!$(this).hasClass('theme-default')) {
          $(this).addClass('checked');
        }
        var selectedTheme = jQuery(this).attr('data-attr');
        jQuery("#edit-theme").val(selectedTheme).change();
      });
    }
  };
})(jQuery, Drupal);
