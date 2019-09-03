(function ($, Drupal) {
  Drupal.behaviors.custom = {
    attach: function (context, settings) {
      if ($(window).width() > 767) {
        $(".nav li.expanded", context).hover(
          function(){
            $(this).addClass("open");
          },function(){
            $(this).removeClass("open");
          }
        );
      }
    }
  };
})(jQuery, Drupal);
