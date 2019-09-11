/**
 * @file
 * Alters the destination path in login links.
 */

(function ($, Drupal) {
  /**
   * Alters the destination path in login links.
   */
  function alterLoginDestinationPath() {
    let $loginLinkElements = $("a[href*='user/login']");

    $loginLinkElements.once().attr('href', function () {
      let href = new URL(this.href);
      let params = href.searchParams;

      params.set('destination', window.location.pathname);
      href.search = decodeURIComponent(params.toString());

      return (href.pathname + href.search);
    });
  }

  Drupal.behaviors.osLoginRedirectAlter = {
    attach: function (context, settings) {
      alterLoginDestinationPath();
    }
  };
})(jQuery, Drupal);
