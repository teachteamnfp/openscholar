(function() {
  var m = angular.module('OsNodeForm', ['angularModalService', 'MediaBrowserField', 'formElement', 'os-buttonSpinner']);

  /**
   * Get node form.
   */
  m.service('nodeForm', ['$http', '$q', function ($http, $q) {

    this.getForm = function(bundle) {
      var promises = [];
      var baseUrl = Drupal.settings.paths.api;
      var queryArgs = {};
      if (angular.isDefined(Drupal.settings.spaces)) {
        if (Drupal.settings.spaces.id) {
          queryArgs.vsite = Drupal.settings.spaces.id;
        }
      }
      var config = {
        params: queryArgs
      };
      var promise = $http.get(baseUrl+'/' + bundle +'/form', config).then(function (response) {
        return response.data;
      });
      return promise;
    }

  }])

  /**
   * Open modals for node add forms.
   */
  m.directive('nodeFormModal', ['ModalService', function (ModalService) {
    var dialogOptions = {
      minWidth: 1187,
      minHeight: 100,
      modal: true,
      position: 'center',
      dialogClass: 'ap-settings-form'
    };

    function link(scope, elem, attrs) {

      elem.bind('click', function (e) {

        scope.title = 'Create ' + attrs.nodeType;

        e.preventDefault();
        e.stopPropagation();

        ModalService.showModal({
          controller: 'nodeFormController',
          template: '<div><div class="node-form-loading" ng-show="loading"><div class="node-form-loading-message">Loading form...<br /></div></div>'+
                    '<div node-form></div></div>',
          inputs: {
            nodeType: attrs.nodeType
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

  m.directive('nodeForm', [function () {
    return {
      restrict: 'AE',
      templateUrl: Drupal.settings.paths.osNodeForm + '/node-form.html',
    };
  }])

  /**
   * The controller for the forms themselves
   */
  m.controller('nodeFormController', ['$scope', '$sce', 'nodeForm', 'buttonSpinnerStatus', 'nodeType', 'close', function ($s, $sce, nodeForm, bss, nodeType, close) {
    $s.formId = nodeType + '_node_form';
    $s.formElements = {};
    $s.formData = {};
    $s.status = [];
    $s.errors = [];
    $s.showSaveButton = true;
    $s.loading = true;

    nodeForm.getForm(nodeType).then(function(response) {
      var formElementsRaw = response.data;
      $s.loading = false;
      for (var formElem in formElementsRaw) {
        $s.formData[formElem] = formElementsRaw[formElem]['#default_value'] || null;
        var attributes = {
          name: formElem
        };
        for (var key in formElementsRaw[formElem]) {
          var elem = key;
          if (key.indexOf('#') === 0) {
            key = key.substr(1, key.length);
          }
          attributes[key] = formElementsRaw[formElem][elem];
        }
        $s.formElements[formElem] = attributes;
      }
    });
  }]);

})()
