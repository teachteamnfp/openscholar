(function ($, Drupal) {
  Drupal.behaviors.custom = {
    attach: function (context, settings) {
      let classes = ["bg-one", "bg-two", "bg-three", "bg-four", "bg-five", "bg-six", "bg-seven", "bg-eight"];
      let body = $('body.not-front');
      $(body, context).addClass(classes[~~(Math.random() * classes.length)]);
    }
  };
})(jQuery, Drupal);
