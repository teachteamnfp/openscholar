(function ($, Drupal) {
  Drupal.behaviors.theme_picker = {
    attach: function (context, settings) {
      jQuery('.theme-selector .theme-screenshot').click(function () {
        jQuery('.theme-selector').removeClass('checked');
        if (!$(this).parents('.theme-selector').hasClass('theme-default')) {
          $(this).parents('.theme-selector').addClass('checked');
        }
        var selectedTheme = jQuery(this).parents('.theme-selector').attr('data-attr');
        console.log(selectedTheme);
        jQuery("#edit-theme").val(selectedTheme).change();
      });
    }
  };
})(jQuery, Drupal);
