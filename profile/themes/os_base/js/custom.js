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
    }
  };
})(jQuery, Drupal);
