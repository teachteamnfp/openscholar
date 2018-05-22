(function () {
  var m = angular.module('osHelp', []);

  m.directive('documentation', [function () {
    return {
      templateUrl: Drupal.settings.paths.documentation,
      link: function (scope) {
        scope.base_url = window.location.origin+Drupal.settings.basePath;
      }
    };
  }]);
})();
