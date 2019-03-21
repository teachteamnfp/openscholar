(function (document) {

  var moduleListElem = document.querySelector('body[data-ng-modules]');
  if (moduleListElem !== false) {
    moduleList = moduleListElem.attributes['data-ng-modules'].value.split(',');

    angular.module('openscholar', moduleList);
  }
})(document);