(function ($) {

  Drupal.behaviors.osWidgetsRandom = {
    attach: function (ctx) {

      $('.widget-collection-random', ctx).each(function () {
        let elems = $(this).find('> div'),
          key = Math.floor(Math.random() * elems.length);
        $(elems[key]).show();
      });

    }
  }

})(jQuery);