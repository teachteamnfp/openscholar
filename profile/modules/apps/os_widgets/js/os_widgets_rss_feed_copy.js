(function ($, Drupal) {
  Drupal.behaviors.osBoxesRss = {
    attach: function (context, settings) {
      $('.rss-feed-link', context).click(function (e) {
        e.preventDefault();
        var rss = document.createElement("input");
        rss.setAttribute("value", $(this).attr('href'));
        document.body.appendChild(rss);
        rss.select();
        document.execCommand("copy");
        document.body.removeChild(rss);
        $(this).html(Drupal.t('Feed URL copied to clipboard'));
      });
    }
  }

})(jQuery, Drupal);
