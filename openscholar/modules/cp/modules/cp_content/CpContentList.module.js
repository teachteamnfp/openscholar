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
   * Fetches the settings forms from the server and makes them available directives and controllers
   */
  m.service('cpFetchContent', ['$http', function ($http) {

    var service = {
      getData: function(page, count, sorting, filter, vsite) {
        var baseUrl = Drupal.settings.paths.api;
        if (filter != undefined) {
          filterOperator = filter.filterOperator;
          filterField = filter.filterField;
          filterValue = filter.filterValue;
          filter = "filter["+filterField+"][value]="+filterValue+"&filter["+filterField+"][operator]="+filterOperator;
        } else {
          filter = '';
        }
        var params = {
          page: page,
          vsite: vsite,
          range: count,
          sort: sorting,
        };
        var config = {
          params: params
        }
        var promise = $http.get(baseUrl+'/nodes?'+filter, config)
          .success(function(response) {
          });
        return promise.then(function(result) {
          return result;
        });
      }
    }

    return service;

  }]);

  /**
   * Fetching cp content and fill it in setting form modal.
   */
  m.directive('cpContent', ['NgTableParams', 'cpFetchContent', function (NgTableParams, cpFetchContent) {
    function link(scope, element, attrs) {
      // Fetch the vsite id.
      if (Drupal.settings.spaces != undefined) {
        if (Drupal.settings.spaces.id) {
          var vsite = Drupal.settings.spaces.id;
        }
      }
      // Hide Defaut buttons from ApSettings Modal form.
      scope.$parent.$parent.$parent.showSaveButton = false;
      var tableData = function (filter) {
        scope.tableParams = new NgTableParams({
          page: 1,
          count: 10,
          sorting : {
            changed : 'desc'
          }},
          {
            total: 0,
            counts: [], //hide page counts control.
            getData: function(params) {
            var orderBycolumn = params.orderBy();
            var sortNameValue = orderBycolumn[0].replace(/\+/g, "");
            return cpFetchContent.getData(params.page(), params.count(), sortNameValue, filter, vsite).then(function(responce) {
              params.total(responce.data.count);
              return responce.data.data;
            });
          }
        });
      }
      // Get default content.
      tableData();
      // Search button: Filter data by title, content-type, taxonomy.
      scope.search = function () {
        var filter = '';
        if (scope.label) {
          filter = {
            filterField : 'label',
            filterValue : scope.label,
            filterOperator : 'CONTAINS'
          };
        }
        // Get filtered content.
        tableData(filter);
      };

    }

    return {
      link: link,
      templateUrl: function () {
        return Drupal.settings.paths.cpContent + '/cp_content.html'
      },
    };
    }]);
})();
