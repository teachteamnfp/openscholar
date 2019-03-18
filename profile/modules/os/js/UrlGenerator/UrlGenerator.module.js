(function (ng) {

  let m = ng.module('UrlGenerator', ['DrupalSettings']);

  m.factory('urlGenerator', ['$location', 'drupalSettings', function ($l, settings) {
    let urlGenerator = function () {
      this.installRoot = $l.protocol() + '://'+$l.host();
      if ($l.port() != 80) {
        this.installRoot += ':'+$l.port();
      }
      this.installRoot += settings.fetchSetting('path.baseUrl');
    };

    urlGenerator.prototype.generate = function (path, vsite) {
      let url = this.installRoot;
      if (vsite && settings.hasSetting('spaces')) {
        url += settings.fetchSetting('spaces.purl') + '/';
      }
      return url + path;
    };

    return new urlGenerator();
  }]);

})(angular);