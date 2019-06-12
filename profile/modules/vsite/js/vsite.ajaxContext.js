
(function (Drupal, drupalSettings) {

  var origBeforeSend = Drupal.Ajax.prototype.beforeSend;

  Drupal.Ajax.prototype.beforeSend = function (xmlhttprequest, options) {
    origBeforeSend.call(this, xmlhttprequest, options);
    // skip if we're not on a space
    if (drupalSettings.spaces != undefined) {

      // change the url
      var url = options.url,
        basePath = drupalSettings.path.baseUrl,
        vsite = drupalSettings.spaces.purl,
        pattern = new RegExp('^' + basePath.replace('/', '\\/')),
        dupePattern = new RegExp('^' + basePath.replace('/', '\\/') + vsite);

      if (!url.match(dupePattern)) {
        url = url.replace(pattern, basePath + vsite + '/');
        options.url = url;
      }
    }
  };

  /**
   * Returns the URL to a Drupal page.
   *
   * @param {string} path
   *   Drupal path to transform to URL.
   *
   * @return {string}
   *   The full URL.
   */
  let originalUrl = Drupal.url;
  Drupal.url = function(path) {
    if (drupalSettings.spaces != undefined && path.indexOf(drupalSettings.spaces.purl) === false) {
      return drupalSettings.path.baseUrl + drupalSettings.spaces.purl + '/' + drupalSettings.path.pathPrefix + path;
    }
    return drupalSettings.path.baseUrl + drupalSettings.path.pathPrefix + path;
  };

  for (let k in originalUrl) {
    if (originalUrl.hasOwnProperty(k)) {
      Drupal.url[k] = originalUrl[k];
    }
  }

})(Drupal, window.drupalSettings);