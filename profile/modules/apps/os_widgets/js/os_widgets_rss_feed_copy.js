(function ($, Drupal) {
  Drupal.behaviors.osBoxesRss = {
    attach: function (context, settings) {
      // Find available RssWidget markup and copy generated link to clipboard.
      $('.rss-feed-link', context).click(function (e) {
        e.preventDefault();
        var rss = document.createElement("input");
        // Get copiable link from href value.
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
