(function ($, Drupal) {
  Drupal.behaviors.custom = {
    attach: function (context, settings) {
      $(".nav li.expanded").hover(
        function(){
          $(this).addClass("open");
        },function(){
          $(this).removeClass("open");
        }
      );
      // mobile header toggle for search and secondary menu
      // adding dummy classes needs to replace with actual blocks
      $(".mobile-menu .search").click(function(){
        $(".search-block").collapse('toggle');
      });
      $(".mobile-menu .secondary").click(function () {
        $("nav.secondary").collapse('toggle');
      });
    }
  };
})(jQuery, Drupal);
