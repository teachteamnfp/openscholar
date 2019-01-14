
(function (Drupal, drupalSettings) {

  var origBeforeSend = Drupal.Ajax.prototype.beforeSend;

  Drupal.Ajax.prototype.beforeSend = function (xmlhttprequest, options) {
    origBeforeSend.call(this, xmlhttprequest, options);

    // change the url
    var url = options.url,
      basePath = drupalSettings.path.baseUrl,
      vsite = 'site01',
      pattern = new RegExp('^'+basePath.replace('/', '\\/')),
      dupePattern = new RegExp('^'+basePath.replace('/', '\\/')+vsite);

    if (!url.match(dupePattern)) {
      url = url.replace(pattern, basePath + vsite + '/');
      options.url = url;
    }
  }

})(Drupal, window.drupalSettings);