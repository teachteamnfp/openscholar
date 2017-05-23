(function () {
  angular.module('CpContentList', ['ngTable', 'ui.sortable'])
    .config(['$injector', function ($injector) {
      try {
        depManager = $injector.get('DependenciesProvider');
      }
      catch (err) {
      }
    }])
    .directive('cpContentList', ['$http', function ($http) {
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
      
      return {
        controller: function ($scope) {
          $http.get(baseUrl+'/contents', config).then(function (response) {
            $scope.contentTable  = new NgTableParams({page: 1, count: 5}, {dataset: response.data});
          });
        },
        templateUrl: function () {
          return Drupal.settings.paths.cpContent + '/content_list.html'
        },
      };
    }]);

})();
