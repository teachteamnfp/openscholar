(function() {

  var m = angular.module('OsNodeForm', ['angularModalService', 'MediaBrowserField', 'formElement', 'os-buttonSpinner']);

  /**
   * Get node form.
   */
  m.service('nodeFormService', ['$http', '$q', function ($http, $q) {

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

    this.nodeSave = function (node) {
      console.log(node);

    }

  }]);

  /**
   * Open modals for node add forms.
   */
  m.directive('nodeFormModal', ['ModalService', function (ModalService) {
    var dialogOptions = {
      minWidth: 1187,
      minHeight: 100,
      modal: false,
      position: 'center',
      dialogClass: 'ng-node-form'
    };

    function link(scope, elem, attrs) {

      elem.bind('click', function (e) {
       // Dirty Fix: can't edit fields of CKEditor in jQuery UI modal dialog.
       jQuery('<div id="overlay" class="ui-widget-overlay" />').insertBefore(elem);

        scope.title = 'Create ' + attrs.nodeType;

        e.preventDefault();
        e.stopPropagation();

        ModalService.showModal({
          controller: 'nodeFormController',
          templateUrl: Drupal.settings.paths.osNodeForm + '/node-form.html',
          inputs: {
            nodeType: attrs.nodeType
          }
        })
        .then(function (modal) {
          dialogOptions.title = scope.title;
          dialogOptions.close = function (event, ui) {
            //Remove the overlay div element.
            jQuery("#overlay").remove();
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
  m.controller('nodeFormController', ['$scope', '$sce', 'nodeFormService', 'buttonSpinnerStatus', 'nodeType', 'close', function ($s, $sce, nodeFormService, bss, nodeType, close) {

    $s.formId = nodeType + '_node_form';
    $s.formElements = {};
    $s.formData = {};
    $s.status = [];
    $s.errors = [];
    $s.showSaveButton = true;
    $s.loading = true;

    nodeFormService.getForm(nodeType).then(function(response) {
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

    function submitForm($event) {
      bss.SetState('node_form', true);
      console.log($s.formData);
      /*nodeFormService.nodeSave($s.formData).then(function (response) {
        console.log(response);
        bss.SetState('node_form', false);
      }, function (error) {
        $s.errors = [];
        $s.status = [];
        $s.errors.push("Sorry, something went wrong. Please try another time.");
        bss.SetState('node_form', false);
      });*/
    }
    $s.submitForm = submitForm;

    $s.close = function (arg) {
      close(arg);
    }

  }]);

})()
