(function ($) {

  Drupal.behaviors.osWidgetTabs = {
    attach: function (context) {
      $('.widget-collection-tabs', context).tabs();
    }
  }

})(jQuery);