(function ($, Drupal) {
  Drupal.behaviors.theme_picker = {
    attach: function (context, settings) {
      $('.theme-selector .theme-screenshot').click(function () {
        let parent = $(this).parent();
        $('.theme-selector').removeClass('checked');
        if (!parent.hasClass('theme-default')) {
          parent.addClass('checked');
        }
        var selectedTheme = parent.attr('data-attr');
        $("#edit-theme").val(selectedTheme).change();
      });
    }
  };
})(jQuery, Drupal);
