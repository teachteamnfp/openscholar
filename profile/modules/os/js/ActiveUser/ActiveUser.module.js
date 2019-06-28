(function () {
  let m = angular.module('ActiveUser', ['DrupalSettings', 'UrlGenerator']);

  m.service('ActiveUserService', ['$http', '$q', 'drupalSettings', 'urlGenerator', function ($http, $q, settings, url) {
    let user = {
      uid: settings.fetchSetting('user.uid'),
      name: '',
      permissions: {}
    };
    let restPath = url.generate(settings.fetchSetting('paths.api'));

    let deferred;
    this.init = function () {
      deferred = $q.defer();
      $http.get(restPath+'/user/'+user.uid+'?_format=json').then(function (resp) {
        user = resp.data;
        deferred.resolve(angular.copy(user));
      });
    };

    this.getUser = function (callback) {
      deferred.promise.then(callback);
    };
    this.init();

  }]);
})();