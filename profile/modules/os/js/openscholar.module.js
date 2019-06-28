(function (document) {

  var moduleListElem = document.querySelector('body[data-ng-modules]');
  if (moduleListElem !== false) {
    moduleList = [];

    angular.module('openscholar', drupalSettings.angularModules);
  }
})(document);