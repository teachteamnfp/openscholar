(function ($, Drupal) {
  Drupal.behaviors.theme_picker = {
    attach: function (context, settings) {
      $('.theme-selector .theme-screenshot').click(function () {
        var parent = $(this).parents('.theme-selector');
        $('.theme-selector').removeClass('checked');
        if (!parent.hasClass('theme-default')) {
          parent.addClass('checked');
        }
        var selectedTheme = $(this).parents('.theme-selector').attr('data-attr');
        $("#edit-theme").val(selectedTheme).change();
      });
    }
  };
})(jQuery, Drupal);
