(function() {

  var m = angular.module('OsNodeForm', ['angularModalService', 'MediaBrowserField', 'formElement', 'os-buttonSpinner']);

  /**
   * Get node form.
   */
  m.service('nodeFormService', ['$http', '$q', function ($http, $q) {
    
    var baseUrl = Drupal.settings.paths.api;
    this.getForm = function(bundle, nid) {
      var queryArgs = {};
      if (angular.isDefined(Drupal.settings.spaces)) {
        if (Drupal.settings.spaces.id) {
          queryArgs.vsite = Drupal.settings.spaces.id;
        }
      }
      // Edit node form.
      if (nid) {
        queryArgs.nid = nid;
      }
      var config = {
        params: queryArgs
      };
      // Rest call to get a node form.
      var deferred = $q.defer();
      $http.get(baseUrl + '/' + bundle +'/form', config).then(function (response) {
        deferred.resolve(response.data);
      });

      return deferred.promise;
      
    }

    this.save = function (bundle, node, nid) {
      // Assign vsite.
      if (Drupal.settings.spaces) {
        node.og_group_ref = Drupal.settings.spaces.id;
      }
      var deferred = $q.defer();
      // Node edit.
      if (nid) {
        $http.patch(baseUrl + '/' + bundle + '/' + nid, node).then(function (res) {
          deferred.resolve(res);
        }, function(err) {
          deferred.reject(err);
        });
      } else {
        $http.post(baseUrl + '/' + bundle, node).then(function (res) {
          deferred.resolve(res);
        }, function(err) {
          deferred.reject(err);
        });
      }
      return deferred.promise;
    }

    this.delete = function(bundle, nid) {
      return $http.delete(baseUrl + '/' + bundle + '/' + nid)
        .success(function (resp) {
          return resp.data;
      })
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
        // Since we are using Jquery UI dialog to open modal window and on top of
        // that we have created ckeditor instance. Now if we open CKEditor dialog
        // forms (e.g table, mathjax etc) which also use Jquery UI dialog, In 
        // this case Jquery UI dialog unable focus on ckeditor dialog forms. So 
        // basically CKEditor dialog forms not accessible when in a modal dialog.
        // It's a known issue and discussed here
        // https://forum.jquery.com/topic/can-t-edit-fields-of-ckeditor-in-jquery-ui-modal-dialog.
        jQuery('<div id="overlay" class="ui-widget-overlay" />').insertBefore(elem.parent().parent().parent());


        scope.title = (attrs.nid) ? attrs.nodetitle : 'Create ' + attrs.nodeFormModal;
        var inputs = {
          nodeType: attrs.nodeFormModal
        };
        inputs.nid = (attrs.nid) ? attrs.nid : 0;

        e.preventDefault();
        e.stopPropagation();

        ModalService.showModal({
          controller: 'nodeFormController',
          templateUrl: Drupal.settings.paths.osNodeForm + '/node-form.html',
          inputs: inputs
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
  m.controller('nodeFormController', ['$scope', '$sce', 'nodeFormService', 'buttonSpinnerStatus', 'nodeType', 'nid', 'close', '$rootScope', '$timeout', function ($s, $sce, nodeFormService, bss, nodeType, nid, close, $rootScope, $timeout) {

    $s.formId = nodeType + '_node_form';
    $s.deleteAccess = false;
    $s.formElements = {};
    $s.formData = {};
    $s.status = [];
    $s.errors = [];
    $s.showSaveButton = true;
    $s.loading = true;

    nodeFormService.getForm(nodeType, nid).then(function(res) {
      if (nid) {
        // @Todo: node delete access checks needs to be done here.
        $s.deleteAccess = true;
        $s.nid = nid;
      }
      var formElementsRaw = res.data;
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

    $s.submitForm = function ($event) {
      bss.SetState('node_form', true);
      nodeFormService.save(nodeType, $s.formData, nid).then(function (res) {
        $rootScope.$broadcast("success", res.data);
        if (res.status == 200 || res.status == 201) {
          window.location.href = Drupal.settings.basePath + res.data.data[0].path;
          bss.SetState('node_form', false);
        }
        $s.errors = [];
      }, function (err) {
        $s.errors = [];
        $s.status = [];
        $s.errors.push(err.data.title);
        $rootScope.$broadcast("error", err.data);
        bss.SetState('node_form', false);
      });
    }

    // Show Undo div to user for 8 seconds on delete.
    $s.deleteUndoAction = true;
    $s.deleteUndoMessage = true;
    $s.entityDelete = function(nid) {
      $s.deleteUndoAction = !$s.deleteUndoAction;
      timer = $timeout(function() {
        $s.deleteUndoAction = !$s.deleteUndoAction;
        $s.deleteNodeOnClose(nid);
      }, 8000);
    }

    $s.deleteUndo = function() {
      $timeout.cancel(timer);
      $s.deleteUndoAction = true;
      $s.deleteUndoMessage = !$s.deleteUndoMessage;
      timer = $timeout(function() {
        $s.deleteUndoMessage = true;
      }, 2000);
    };

    $s.deleteUndoMessageBoxClose = function() {
      $s.deleteUndoMessage = true;
    };
    
    var node_id;
    $s.deleteNodeOnClose = function(nid) {
      if (nid) {
        node_id = nid; //Assign nid to global scope for future use.
        nodeFormService.delete(nodeType, nid).then(function (res) {
          if (res.status == 200) {
            window.location.href = '/' + Drupal.settings.pathPrefix;
          }
        });
      }
    }

    $s.close = function (arg) {
      //Remove the overlay div element.
      jQuery("#overlay").remove();
      close(arg);
      $s.deleteNodeOnClose(node_id);
    }

  }]);

})()
