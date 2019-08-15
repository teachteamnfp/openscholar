(function ($) {

  Drupal.behaviors.osWidgetsAccordion = {
    attach: function (context) {
      $('.widget-collection-accordion', context).accordion();
    }
  }

})(jQuery);