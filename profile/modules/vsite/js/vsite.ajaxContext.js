
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
  }

})(Drupal, window.drupalSettings);