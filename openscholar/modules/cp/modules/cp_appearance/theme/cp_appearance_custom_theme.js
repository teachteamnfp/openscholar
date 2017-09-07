(function () {

  var m = angular.module('CpAppearanceCustomTheme', ['angularModalService', 'formElement', 'os-buttonSpinner']);

    /**
   * Fetches the content settings forms from the server and makes them available to directives and controllers
   */
  m.service('customTheme', ['$http', '$q', function ($http, $q, $httpParamSerializer) {

  }])

  /**
   * Open modals for the custom theme upload
   */
  m.directive('cpAppearanceCustomTheme', ['ModalService', 'customTheme', function (ModalService, customTheme) {
    var dialogOptions = {
      minWidth: 850,
      minHeight: 100,
      modal: true,
      position: 'center',
      dialogClass: 'ap-settings-form'
    };

    function link(scope, elem, attrs) {
      //apSettings.SettingsReady().then(function () {
        scope.title = 'Themes';
      //});

      elem.bind('click', function (e) {
        e.preventDefault();
        e.stopPropagation();

        ModalService.showModal({
          controller: 'customThemeController',
          template: 
             '<h4>Download the <a target="_blank" href="https://github.com/openscholar/starterkit">Subtheme Starter Kit</a> to begin developing your customtheme.</h4> Use of the custom theme feature is at your own risk. The OpenScholar team is not responsible for maintaining, fixing or updating custom themes uploaded to the system. We will make every attempt possible to publish changes made to the markup used throughout OpenScholar from one code release to the next.' +
             '<ul class=""><li class="clearfix"><span class="label">'+
             '<a href="#">Zip</a></span><div class="description">Upload zip files.</div></li><li class="clearfix">'+
             '<span class="label"><a href="#">Git</a></span><div class="description">'+
             'Clone from a repository.</div></li></ul>',
          inputs: {
            form: scope.form
          }
        })
        .then(function (modal) {
          dialogOptions.title = scope.title;
          dialogOptions.close = function (event, ui) {
            modal.element.remove();
          }
          modal.element.dialog(dialogOptions);
          modal.close.then(function (result) {
            if (result) {
              window.location.reload();
            }
          });
        });
      });
    }

    return {
      link: link,
      scope: {
        form: '@'
      }
    };
  }]);
  
  /**
   * The controller for the forms themselves
   */
  m.controller('customThemeController', ['$scope', '$sce', 'customTheme', 'buttonSpinnerStatus', 'form', 'close', function ($s, $sce, customTheme, bss, form, close) {

  }]);

})()
