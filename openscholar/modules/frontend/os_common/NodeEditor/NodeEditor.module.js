(function ($) {
  var m = angular.module('NodeEditor', []);
  m.directive('nodeEdit', [function () {
    return {
      scope: {
          id :  '@'
        },
      restrict: 'A',
      link: function (scope, elem, attrs) {
        elem.bind('click', function(e) {
          e.preventDefault();
          e.stopPropagation();
          window.location.href = window.location.href.split('#')[0] + '#overlay=' +  Drupal.settings.pathPrefix + 'node/' + scope.id +'/edit';
        });
      }
    };
  }])

})(jQuery);
