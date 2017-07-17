(function() {
  var m = angular.module('CpContent', ['ui.bootstrap', 'JSPager', 'ngTable', 'ngMaterial', 'EntityService']);

  /**
   * Initialize config.
   */
  m.config(function() {
    restPath = Drupal.settings.paths.api;
  });

  /**
   * Fetch filter options.
   */
  m.service('cpFetch', ['$http', function($http) {
    var service = {
      getData: function(endpoint, sorting, filter) {
        var params = {};
        if (angular.isDefined(sorting)) {
          params.sort = sorting;
        }
        if (angular.isUndefined(filter)) {
          filters = '';
        }
        // Fetch the vsite id.
        if (angular.isDefined(Drupal.settings.spaces)) {
          if (Drupal.settings.spaces.id) {
            params.vsite = Drupal.settings.spaces.id;
          }
        }
        var config = {
          params: params
        }
        var promise = $http.get(restPath + '/' + endpoint + '?' + filter, config).then(
          function successCallback(response) {
            return response.data;
          },
          function errorCallback(response) {
            return response.data;
          });

        return promise;
      }
    }

    return service;

  }]);

  /**
   * Open modals for cp content listing.
   */
  m.directive('cpContentModal', ['ModalService', function(ModalService) {
    var dialogOptions = {
      minWidth: 850,
      minHeight: 100,
      modal: true,
      position: 'center',
      dialogClass: 'ap-settings-form'
    };

    function link(scope, elem, attrs) {

      elem.bind('click', function(e) {
        e.preventDefault();
        e.stopPropagation();

        ModalService.showModal({
            controller: "cpModalController",
            template: '<form id="cp-content"><div cp-content></div></form>',
          })
          .then(function(modal) {
            dialogOptions.title = 'Content';
            dialogOptions.close = function(event, ui) {
              modal.element.remove();
            }
            modal.element.dialog(dialogOptions);
          });
      });
    }

    return {
      link: link
    };
  }]);

  m.controller('cpModalController', function($scope, $timeout, $filter, $rootScope, close, EntityService, cpFetch, NgTableParams) {
    $scope.close = function(arg) {
      close(arg);
    }

    $scope.message = false;

    var nodeService = new EntityService('nodes', 'id');
    // Fetch vsite home.
    if (Drupal.settings.paths.vsite_home != undefined) {
      $scope.vsiteUrl = Drupal.settings.paths.vsite_home;
    }

    $scope.closeMessage = function() {
      $scope.message = false;
    }

    // Reset select all checkboxes.
    $scope.resetCheckboxes = function() {
      $scope.checkboxes.checked = false;
      angular.forEach($scope.checkboxes.items, function(value, key) {
        $scope.checkboxes.items[key] = false;
      });
    }

    $scope.getMatchedTaxonomyTerms = function(termOperation) {
      var i = 0;
      angular.forEach($scope.selectedItems, function(nid, key) {
        if (nid) {
          filter += '&entity_id[' + i + ']=' + key + '&entity_type=node';
          i++;
        }
      });
      var filter = (termOperation) ? filter : '';
      return cpFetch.getData('taxonomy', 'label', filter).then(function(responce) {
        return responce;
      });
    }

    $scope.nodeTermOperation = function(operation) {
      var nids = [];
      var terms = [];
      var tids = (operation == 'applyTerm') ? $scope.applyTermModel : $scope.removeTermModel;
      angular.forEach($scope.selectedItems, function(value, key) {
        if (value) {
          nids.push(key);
        }
      });
      angular.forEach(tids, function(obj, key) {
        terms.push(obj.id);
      });
      var entity = {
        entity_type: 'node',
        entity_id: nids,
        tid: terms
      };
      if (operation == 'applyTerm') {
        nodeService.applyTermToNodes(entity).then(function(responce) {
          if (responce.data.data.saved) {
            $scope.message = 'Terms have been applied to selected content.';

          } else {
            $scope.message = 'Please select a term to be applied.';
          }
        });

      } else {
        nodeService.removeTermFromNodes(entity).then(function(responce) {
          if (responce.data.data.saved) {
            $scope.message = 'Terms have been removed from selected content.';
          } else {
            $scope.message = 'Please select a term to be removed.';
          }
        });

      }

    }

    // Search button: Filter data by title, content-type, taxonomy.
    $scope.search = function() {
      $scope.message = false;
      var filter = {};
      if ($scope.label) {
        filter.label = $scope.label;
        $scope.tableParams.filter(filter);
      }
      if ($scope.contentTypeModel.length > 0) {
        var selectedType = [];
        angular.forEach($scope.contentTypeModel, function(value, key) {
          selectedType.push($scope.contentTypeModel[key].id);
        });
        filter.type = selectedType;
        $scope.tableParams.filter(filter);
      }
      if ($scope.taxonomyTermsModel.length > 0) {
        var selectedTerms = [];
        angular.forEach($scope.taxonomyTermsModel, function(value, key) {
          selectedTerms.push($scope.taxonomyTermsModel[key].id);
        });
        filter.og_vocabulary = selectedTerms;
        $scope.tableParams.filter(filter);
      }
    };


    // Bulk node operation.
    $scope.nodeBulkOperation = function(operation) {
      var nids = [];
      angular.forEach($scope.selectedItems, function(selectedItem, nid) {
        if (selectedItem) {
          nids.push(parseInt(nid));
        }
      });
      var entity = {
        entity_type: 'node',
        entity_id: nids,
        operation: operation
      }
      // Bulk operation DELETE should go through Undo option. 
      if (operation == 'deleted') {
        $scope.nodeDelete(nids);
      } else {
        $scope.changePublishStatusToggle(nids, operation);
      }
    }

    $scope.changePublishStatusToggle = function(nid, publish_status) {
      var nodeId = angular.isArray(nid) ? nid : [nid];
      var operation = (publish_status) ? 'published' : 'unpublished';
      var entity = {
        entity_type: 'node',
        entity_id: nodeId,
        operation: operation
      }
      nodeService.bulkOperation(entity).then(function(responce) {
        if (responce.data.data.saved) {
          $scope.message = 'Selected content has been ' + operation + '.';
          angular.forEach($scope.tableParams.data, function(node, key) {
            if (nodeId.indexOf(node.id) > -1) {
              $scope.tableParams.data[key].publish_status = publish_status;
            }
          });
        }
      });
    };

    // Show Undo div to user for 8 seconds on delete.
    $scope.deleteUndoAction = true;
    $scope.deleteUndoMessage = true;
    var nodeId, timer, list;
    var oldList = [];
    $scope.nodeDelete = function(nid) {
      if (angular.isArray(nid)) {
        nodeId = nid;
      } else {
        nodeId = [nid];
      }
      var newDataList = [];
      if (oldList.length > 0) {
        list = oldList;
        oldList = [];
      } else {
        list = $scope.tableParams.data;
      }
      angular.forEach(list, function(node) {
        if (nodeId.indexOf(node.id) == -1) {
          newDataList.push(node);
        }
        oldList.push(node);
      });
      $scope.tableParams.data = newDataList;
      $scope.deleteUndoMessage = true;
      $scope.deleteUndoAction = !$scope.deleteUndoAction;
      timer = $timeout(function() {
        $scope.deleteUndoAction = !$scope.deleteUndoAction;
        var entity = {
          entity_type: 'node',
          entity_id: nodeId,
          operation: 'deleted'
        }
        nodeService.bulkOperation(entity).then(function(responce) {
          if (responce.data.data.saved) {
            $scope.message = 'Selected content has been ' + entity.operation + '.';
          }
        });
      }, 8000);
    };

    $scope.deleteNodeOnClose = function() {
      $timeout.cancel(timer);
      var entity = {
        entity_type: 'node',
        entity_id: nodeId,
        operation: 'deleted'
      }
      nodeService.bulkOperation(entity).then(function(responce) {
        if (responce.data.data.saved) {
          $scope.message = 'Selected content has been deleted.';
          $scope.deleteUndoAction = true;
          $scope.search();
        }
      });
    };

    $scope.deleteUndo = function() {
      $timeout.cancel(timer);
      $scope.deleteUndoAction = true;
      $scope.deleteUndoMessage = !$scope.deleteUndoMessage;
      timer = $timeout(function() {
        $scope.deleteUndoMessage = true;
      }, 3000);
      $scope.tableParams.data = oldList;
    };

    $scope.deleteUndoMessageClose = function() {
      $scope.deleteUndoMessage = true;
    };

    // Fetch list and set it in ng-table;
    cpFetch.getData('nodes').then(function(responce) {
      var data = responce.data;
      $scope.tableParams = new NgTableParams({
        page: 1,
        count: 20,
        sorting: {
          created: 'desc'
        }
      }, {
        total: 0,
        counts: [], // hide page counts control.
        getData: function(params) {
          var typeDataSet = [];
          var termDataSet = [];
          var filteredData = params.filter() ? $filter('filter')(data, params.filter()) : data;
          if (angular.isDefined(params.filter().type)) {
            angular.forEach(data, function(node, key) {
              if (params.filter().type.indexOf(node.type) > -1) {
                typeDataSet.push(node);
              }
            });
            filteredData = typeDataSet;
          }
          if (angular.isDefined(params.filter().og_vocabulary)) {
            angular.forEach(data, function(node, key) {
              if (node.og_vocabulary) {
                angular.forEach(node.og_vocabulary, function(vocab, key) {
                  if (params.filter().og_vocabulary.indexOf(parseInt(vocab.tid)) > -1) {
                    termDataSet.push(node);
                  }
                });
              }

            });

            filteredData = termDataSet;
          }
          if (angular.isDefined(params.filter().label)) {
            filteredData = $filter('filter')(filteredData, params.filter().label);
          }
          var orderedData = params.sorting() ? $filter('orderBy')(filteredData, params.orderBy()) : filteredData;
          params.total(orderedData.length);
          $scope.noRecords = (orderedData.length) == 0 ? true : false;
          return orderedData.slice((params.page() - 1) * params.count(), params.page() * params.count());
        }
      });
    });

  });

  /**
   * Fetching cp content and fill it in setting form modal.
   */
  m.directive('cpContent', ['$rootScope', function($rootScope) {

    function link(scope, element, attrs) {
      // Bulk Operation.
      scope.checkboxes = {
        'checked': false,
        items: {}
      };
      $rootScope.disableApply = true;

      // Watch for check all checkbox.
      scope.$watch('checkboxes.checked', function(value) {
        if (angular.isDefined(scope.tableParams)) {
          angular.forEach(scope.tableParams.data, function(node) {
            if (angular.isDefined(node.id)) {
              scope.checkboxes.items[node.id] = value;
            }
          });
        }

        $rootScope.disableApply = !value;
        scope.selectedItems = scope.checkboxes.items;
      });

      // Watch for data checkboxes.
      scope.$watch('checkboxes.items', function(values) {
        if (angular.isDefined(scope.tableParams)) {
          if (!scope.tableParams.data) {
            return;
          }
          var checked = 0,
            unchecked = 0,
            total = scope.tableParams.data.length;
          angular.forEach(scope.tableParams.data, function(node) {
            checked += (scope.checkboxes.items[node.id]) || 0;
            unchecked += (!scope.checkboxes.items[node.id]) || 0;
          });
          if ((unchecked == 0) || (checked == 0)) {
            scope.checkboxes.checked = (checked == total);
          }
          if (checked > 0) {
            $rootScope.disableApply = false;
            scope.disableApply = $rootScope.disableApply;
          } else {
            $rootScope.disableApply = true;
            scope.disableApply = $rootScope.disableApply;
            scope.checkboxes.checked = false;
          }
          // Grayed checkbox.
          angular.element(document.getElementById("select_all")).prop("indeterminate", (checked != 0 && unchecked != 0));
        }

      }, true);

      // Initialize apply taxonomy term dropdown.
      scope.applyTermModel = [];
      scope.applyTermSettings = {
        scrollable: true,
        smartButtonMaxItems: 2,
        termDropdown: true,
        termOperation: true,
        buttonClasses: ''
      };

      // Initialize remove taxonomy term dropdown.
      scope.removeTermModel = [];
      scope.removeTermSettings = {
        scrollable: true,
        smartButtonMaxItems: 2,
        termDropdown: true,
        termOperation: true,
        buttonClasses: ''
      };

      // Initialize content type filter.
      scope.contentTypeModel = [];
      scope.contentTypeSettings = {
        scrollable: true,
        smartButtonMaxItems: 2,
        showAllConentTypeCheckBox: true,
        selectAllDefault: true
      };
      scope.contentTypeTexts = {
        buttonDefaultText: 'All content types',
      }
      if (angular.isDefined(Drupal.settings.cpContent.contentTypeOptions)) {
        scope.contentTypes = Drupal.settings.cpContent.contentTypeOptions;
      }

      scope.selectAllFlag = false;
      $rootScope.$watch('selectAllFlag', function(newValue) {
        scope.selectAllFlag = newValue;
      });

      // Initialize taxonomy term filter.
      scope.taxonomyTermsModel = [];
      scope.taxonomyTermsSettings = {
        scrollable: true,
        smartButtonMaxItems: 2,
        termDropdown: true,
        termOperation: false,
      };
      scope.taxonomyTermsTexts = {
        buttonDefaultText: 'Taxonomy Terms'
      };
      scope.taxonomyTermsOptions = [];

      // Delete node.
      scope.showPopover = false;
      scope.popOver = function($event, nid) {
        if (angular.isDefined(nid)) {
          scope.showPopover = nid;
          $event.stopPropagation();
          $event.preventDefault();
        }
      };

      window.onclick = function() {
        if (scope.showPopover) {
          scope.showPopover = false;
          scope.$apply();
        }
      };
    }

    return {
      link: link,
      templateUrl: function() {
        return Drupal.settings.paths.cpContent + '/cp_content.html'
      },
    };
  }]);

  m.directive('cpContentDropdownMultiselect', ['$rootScope', '$filter', '$document', '$compile', '$parse', 'EntityService',

    function($rootScope, $filter, $document, $compile, $parse, EntityService) {

      return {
        restrict: 'AE',
        scope: {
          selectedModel: '=',
          options: '=',
          extraSettings: '=',
          events: '=',
          translationTexts: '=',
          groupBy: '@'
        },
        templateUrl: function() {
          return Drupal.settings.paths.cpContent + '/cp_content_dropdown.html'
        },
        link: function(scope, element, attrs) {

          scope.checkboxes = attrs.checkboxes ? true : false;
          scope.groups = attrs.groupBy ? true : false;
          scope.displayType = attrs.displayType;
          scope.displayText = attrs.displayText;
          scope.selectAllFlag = true;

          var $dropdownTrigger = element.children()[0];

          scope.externalEvents = {
            onItemSelect: angular.noop,
            onItemDeselect: angular.noop,
            onSelectAll: angular.noop,
            onDeselectAll: angular.noop,
            onInitDone: angular.noop,
            onMaxSelectionReached: angular.noop
          };

          scope.settings = {
            dynamicTitle: true,
            scrollable: false,
            scrollableHeight: '300px',
            closeOnBlur: true,
            displayProp: 'label',
            idProp: 'id',
            externalIdProp: 'id',
            selectionLimit: 0,
            showAllConentTypeCheckBox: false,
            selectAllDefault: false,
            showUncheckAll: true,
            closeOnSelect: false,
            buttonClasses: 'btn btn-default',
            closeOnDeselect: false,
            groupBy: attrs.groupBy || undefined,
            groupByTextProvider: null,
            smartButtonMaxItems: 0,
            termDropdown: false,
            termOperation: false,
            smartButtonTextConverter: angular.noop
          };

          scope.texts = {
            checkAll: 'Check All',
            uncheckAll: 'Uncheck All',
            selectionCount: 'checked',
            selectionOf: '/',
            searchPlaceholder: 'Search...',
            buttonDefaultText: 'Select',
            dynamicButtonTextSuffix: 'checked'
          };
          $rootScope.$watch('disableApply', function(newValue) {
            scope.disableApply = newValue;
          });

          scope.toggleDropdown = function() {
            scope.open = !scope.open;
            if (scope.settings.termDropdown) {
              scope.$parent.$parent.getMatchedTaxonomyTerms(scope.settings.termOperation).then(function(response) {
                if (angular.isDefined(response.status) && response.status == 400) {
                  scope.termErrorMessage = response.title;
                  scope.showTermErrorMessage = true;
                } else {
                  scope.showTermErrorMessage = false;
                  scope.options = response.data;
                }
              });
            }
          };

          // Add term to vocabulary.
          scope.addTerm = function(key, vid, vocabName) {
            if (angular.isDefined(scope.orderedItems[key].termName)) {
              var entity = {
                vid: vid,
                name: scope.orderedItems[key].termName
              }
              var termAddService = new EntityService('taxonomy/term/add', 'id');
              termAddService.add(entity).then(function(responce) {
                if (responce.data.data[0]) {
                  scope.orderedItems.splice(key, 0, {
                    id: responce.data.data[0],
                    label: entity.name,
                    vid: entity.vid,
                    vocabName: vocabName
                  });
                  scope.orderedItems[key].termName = '';
                  scope.$parent.$parent.message = entity.name + ' term have been added to ' + vocabName + ' vocabulary.';
                }
              });
            }
          };

          // Remove selected terms from selected nodes.
          scope.removeTerms = function() {
            scope.$parent.$parent.nodeTermOperation('removeTerm');
            scope.open = false;
          };

          // Add selected terms to selected nodes.
          scope.applyTerms = function() {
            scope.$parent.$parent.nodeTermOperation('applyTerm');
            scope.open = false;
          };

          scope.closeTermDropdown = function() {
            scope.open = !scope.open;
          }

          if (angular.isDefined(scope.settings.groupBy)) {
            scope.$watch('options', function(newValue) {
              if (angular.isDefined(newValue)) {
                scope.orderedItems = $filter('orderBy')(newValue, scope.settings.groupBy);
              }
            });
          }
          angular.extend(scope.settings, scope.extraSettings || []);
          angular.extend(scope.externalEvents, scope.events || []);
          angular.extend(scope.texts, scope.translationTexts);

          var message = {
            failedMessage: 'Something went wrong.'
          }
          scope.stopBubbling = function($event) {
            $event.stopImmediatePropagation();
          };

          scope.termGroup = false;
          scope.groupToggleDropdown = function(vid) {
            scope.termGroup = vid;
          };

          scope.checkboxClick = function($event, id) {
            scope.setSelectedItem(id);
            $event.stopImmediatePropagation();
          };

          if (scope.settings.selectAllDefault) {
            scope.$watch('options', function(newValue) {
              if (angular.isDefined(newValue)) {
                scope.selectAllToggle();
              }
            });
          }

          function getFindObj(id) {
            var findObj = {};

            if (scope.settings.externalIdProp === '') {
              findObj[scope.settings.idProp] = id;
            } else {
              findObj[scope.settings.externalIdProp] = id;
            }

            return findObj;
          }

          function clearObject(object) {
            for (var prop in object) {
              delete object[prop];
            }
          }

          if (scope.settings.closeOnBlur) {
            $document.on('click', function(e) {
              var target = e.target.parentElement;
              var parentFound = false;

              while (angular.isDefined(target) && target !== null && !parentFound) {
                if (_.contains(target.className.split(' '), 'multiselect-parent') && !parentFound) {
                  if (target === $dropdownTrigger) {
                    parentFound = true;
                  }
                }
                target = target.parentElement;
              }

              if (!parentFound) {
                scope.$apply(function() {
                  scope.open = false;
                });
              }
            });
          }

          scope.getGroupTitle = function(groupValue) {
            if (scope.settings.groupByTextProvider !== null) {
              return scope.settings.groupByTextProvider(groupValue);
            }

            return groupValue;
          };

          scope.getButtonText = function() {
            if (scope.selectAllFlag && scope.settings.showAllConentTypeCheckBox) {
              return scope.texts.buttonDefaultText;
            }
            if (scope.settings.dynamicTitle && (scope.selectedModel.length > 0 || (angular.isObject(scope.selectedModel) && _.keys(scope.selectedModel).length > 0))) {
              if (scope.settings.smartButtonMaxItems > 0) {
                var itemsText = [];

                angular.forEach(scope.options, function(optionItem) {
                  if (scope.isChecked(scope.getPropertyForObject(optionItem, scope.settings.idProp))) {
                    var displayText = scope.getPropertyForObject(optionItem, scope.settings.displayProp);
                    var converterResponse = scope.settings.smartButtonTextConverter(displayText, optionItem);

                    itemsText.push(converterResponse ? converterResponse : displayText);
                  }
                });

                if (scope.selectedModel.length > scope.settings.smartButtonMaxItems) {
                  itemsText = itemsText.slice(0, scope.settings.smartButtonMaxItems);
                  itemsText.push('...');
                }

                return itemsText.join(', ');
              } else {
                var totalSelected = angular.isDefined(scope.selectedModel) ? scope.selectedModel.length : 0;
                if (totalSelected === 0) {
                  return scope.texts.buttonDefaultText;
                } else {
                  return totalSelected + ' ' + scope.texts.dynamicButtonTextSuffix;
                }
              }
            } else {
              return scope.texts.buttonDefaultText;
            }
          };

          scope.getPropertyForObject = function(object, property) {
            if (angular.isDefined(object) && object.hasOwnProperty(property)) {
              return object[property];
            }

            return '';
          };

          scope.selectAll = function() {
            scope.deselectAll(false);
            scope.externalEvents.onSelectAll();


            angular.forEach(scope.options, function(value) {
              scope.setSelectedItem(value[scope.settings.idProp], true);
            });
          };

          scope.deselectAll = function(sendEvent) {
            sendEvent = sendEvent || true;

            if (sendEvent) {
              scope.externalEvents.onDeselectAll();
            }
            scope.selectedModel.splice(0, scope.selectedModel.length);
          };

          scope.selectAllToggle = function() {
            if (scope.selectAllFlag) {
              $rootScope.selectAllFlag = false;
              scope.selectAll();
            } else {
              $rootScope.selectAllFlag = true;
              scope.deselectAll();
            }
          };

          scope.setSelectedItem = function(id, dontRemove) {
            var findObj = getFindObj(id);
            var finalObj = null;

            if (scope.settings.externalIdProp === '') {
              finalObj = _.find(scope.options, findObj);
            } else {
              finalObj = findObj;
            }
            dontRemove = dontRemove || false;
            var exists = _.findIndex(scope.selectedModel, findObj) !== -1;

            if (!dontRemove && exists) {
              scope.selectedModel.splice(_.findIndex(scope.selectedModel, findObj), 1);
              scope.externalEvents.onItemDeselect(findObj);
            } else if (!exists && (scope.settings.selectionLimit === 0 || scope.selectedModel.length < scope.settings.selectionLimit)) {
              scope.selectedModel.push(finalObj);
              scope.externalEvents.onItemSelect(finalObj);
            }
            if (scope.settings.showAllConentTypeCheckBox) {
              $rootScope.selectAllFlag = scope.selectedModel.length > 0 ? false : true;
              scope.selectAllFlag = scope.selectedModel.length == scope.options.length ? true : false;
            }

            if (scope.settings.closeOnSelect) scope.open = false;
          };

          scope.isChecked = function(id) {
            return _.findIndex(scope.selectedModel, getFindObj(id)) !== -1;
          };

          scope.externalEvents.onInitDone();
        }
      };
    }
  ]);

  m.filter('removeUnderscore', [function() {
    return function(string) {
      if (!angular.isString(string)) {
        return string;
      }
      return string.replace(/_/g, ' ');
    };
  }]);

})();