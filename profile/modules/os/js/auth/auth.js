/**
 * Handles the XSRF token for all write operations to the REST API
 */

(function () {
  var token = '';

  angular.module('os-auth', ['DrupalSettings'])
    .service('authenticate-token', ['$http', 'drupalSettings', function ($http, settings) {
      this.fetch = function () {
        return $http.get(settings.fetchSetting('path.baseUrl') + 'rest/session/token').then(setToken);
      }
    }])
    .factory('authenticate', ['$q', function ($q) {
      return {
        request: function (config) {
          if (token) {
            switch (config.method) {
              case 'PUT':
              case 'POST':
              case 'PATCH':
              case 'DELETE':
                config.headers['X-CSRF-Token'] = token;
                break;
              default:
                break;
            }

          }
          return config;
        }
      }
    }])
    .config(['$httpProvider', function ($httpProvider) {
        $httpProvider.interceptors.push('authenticate');
    }])
    .run(['authenticate-token', function (at) {
      at.fetch();
    }]);

  function setToken(resp) {
    token = resp.data;
  }
})();
