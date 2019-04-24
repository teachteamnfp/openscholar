(function ($, Drupal) {
  Drupal.behaviors.custom = {
    attach: function (context, settings) {
      var classes = ["bg-one", "bg-two", "bg-three", "bg-four", "bg-five", "bg-six", "bg-seven", "bg-eight"];
      $("body.not-front").each(function() {
        $(this).addClass(classes[~~(Math.random() * classes.length)]);
      });
    }
  };
})(jQuery, Drupal);
