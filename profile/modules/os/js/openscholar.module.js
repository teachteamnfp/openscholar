(function (document) {

    let moduleList = angular.isArray(drupalSettings.angularModules) ? drupalSettings.angularModules : Object.values(drupalSettings.angularModules);
    angular.module('openscholar', moduleList);

})(document);