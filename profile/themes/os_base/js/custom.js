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
      $('.dropmenu-child', context).click(function(e) {
        e.preventDefault();
        $(this).siblings('.dropdown-menu').toggleClass("mopen");
        $(this).toggleClass("mopen");
      });
    }
  };
})(jQuery, Drupal);
