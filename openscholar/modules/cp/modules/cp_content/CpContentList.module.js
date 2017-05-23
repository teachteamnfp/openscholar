(function () {
  angular.module('CpContent', ['ngTable', 'ui.sortable'])
    .config(['$injector', function ($injector) {
      try {
        depManager = $injector.get('DependenciesProvider');
      }
      catch (err) {
      }
    }])
    .directive('cpContent', ['$http', '$filter', '$q', 'NgTableParams', function ($http, $filter, $q, NgTableParams) {
      function link(scope, element, attrs) {
        var queryArgs = {};
        if (Drupal.settings.spaces != undefined) {
          if (Drupal.settings.spaces.id) {
            queryArgs.vsite = Drupal.settings.spaces.id;
          }
        }
        var baseUrl = Drupal.settings.paths.api;
        var config = {
          params: queryArgs
        };

        $http.get(baseUrl+'/nodes', config).then(function(responce) {
          var data = responce.data.data;
          scope.tableParams = new NgTableParams({
              sorting: {
              type: 'asc'
            }
          }, {
            counts: [], //hide page counts control.
            total: responce.data.count,
            dataset: responce.data.data,
            getData: function(params) {
              queryArgs.range = params.count();
              queryArgs.page = params.page();
              params.data = data;
              $http.get(baseUrl+'/nodes', config).then(function(responce) {
                params.data = responce.data.data;
              });
            }
          });
        });
      }

      return {
        link: link,
        templateUrl: function () {
          return Drupal.settings.paths.cpContent + '/cp_content.html'
        },
      };
    }]);
})();
