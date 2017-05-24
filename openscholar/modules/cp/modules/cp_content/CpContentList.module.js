(function () {
  var m = angular.module('CpContent', ['ngTable', 'ui.sortable'])
    .config(['$injector', function ($injector) {
      try {
        depManager = $injector.get('DependenciesProvider');
      }
      catch (err) {
      }
  }])

  /**
   * Fetching cp content and fill it in setting form modal.
   */
  m.directive('cpContent', ['$http', '$filter', '$q', 'NgTableParams', function ($http, $filter, $q, NgTableParams) {
    function link(scope, element, attrs) {
      // Hide Defaut buttons from ApSettings Modal form.
      scope.$parent.$parent.$parent.showSaveButton = false;
      scope.search = function () {
        // Here we play with search operation and filled result into ngTable.
        console.log(scope.label);

      };
      var queryArgs = {
        sort : '-changed'
      };
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
          sorting : {
            changed : 'desc'
          } 
        }, 
        {
          counts: [], //hide page counts control.
          total: responce.data.count,
          getData: function(params) {
            var orderBycolumn = params.orderBy();
            console.log(orderBycolumn);
            queryArgs.range = params.count();
            queryArgs.page = params.page();
            queryArgs.sort = orderBycolumn[0].replace(/\+/g, "");
            // Todo We need to fix this
            // filter[label]=test news'
            //queryArgs.filter = [{label:'test'}];
            params.data = data;
            $http.get(baseUrl+'/nodes', config).then(function(responce) {
              console.log('getData');
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
