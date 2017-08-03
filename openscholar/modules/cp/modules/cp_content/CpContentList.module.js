(function() {
  var messageFailed = 'Something went wrong. Please try again later.';

  var m = angular.module('CpContent', ['ui.bootstrap', 'ngTable', 'ngMaterial', 'EntityService', 'os-buttonSpinner', 'NodeEditor']);

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
            template: '<div><div class="entity-loading" ng-show="loading"><div class="entity-loading-message">Loading content...<br /></div></div>' +
              '<div cp-content entity-type="' + attrs.entityType + '"></div>',
            inputs: {
              entityType: attrs.entityType
            }
          })
          .then(function(modal) {
            dialogOptions.title = 'Content';
            dialogOptions.close = function(event, ui) {
              modal.element.remove();
              window.location.reload();
            }
            modal.element.dialog(dialogOptions);
          });
      });
    }

    return {
      link: link
    };
  }]);

  m.controller('cpModalController', ['$scope', '$timeout', '$filter', '$rootScope', 'EntityService', 'NgTableParams', 'buttonSpinnerStatus', 'entityType', function($scope, $timeout, $filter, $rootScope, EntityService, NgTableParams, buttonSpinnerStatus, entityType) {

    nodeService = new EntityService(entityType, 'id');
    vocabService = new EntityService('vocabulary', 'id');

    $scope.resetCheckboxes = function() {
      $scope.disableBulkOptions = true;
      $scope.checkboxes.checked = false;
      angular.forEach($scope.tableParams.data, function(node) {
        $scope.checkboxes.items[node.id] = false;
      });
    }
    $scope.message = false;
    $scope.loading = true;
    $scope.entityType = entityType;
    // Fetch list and set it in ng-table;
    nodeService.fetch({
      sort: '-changed'
    }).then(function(data) {
      $scope.tableParams = new NgTableParams({
        page: 1,
        count: 24,
        sorting: {
          changed: 'desc'
        }
      }, {
        total: 0,
        counts: [], // hide page counts control.
        getData: function(params) {
          var typeDataSet = [];
          var termDataSet = [];
          var filteredData = data;
          if (angular.isDefined(params.filter().type)) {
            angular.forEach(filteredData, function(node, key) {
              if (params.filter().type.indexOf(node.type) > -1) {
                typeDataSet.push(node);
              }
            });
            filteredData = typeDataSet;
            $scope.disableBulkOptions = (filteredData.length) == 0 ? true : false;
          }
          if (angular.isDefined(params.filter().og_vocabulary)) {
            var vocabTerms = params.filter().og_vocabulary;
            var termDataSet = [];
            angular.forEach(filteredData, function(node, key) {
              if (node.og_vocabulary != null) {
                angular.forEach(vocabTerms, function(tid) {
                  if ($filter('filter')(node.og_vocabulary, tid).length > 0) {
                    termDataSet.push(node);
                  }
                });
              }
            });
            filteredData = $filter('unique')(termDataSet, 'id');
            $scope.disableBulkOptions = (filteredData.length) == 0 ? true : false;
          }
          if (angular.isDefined(params.filter().label)) {
            filteredData = $filter('filter')(filteredData, params.filter().label);
            $scope.disableBulkOptions = (filteredData.length) == 0 ? true : false;
          }

          var orderedData = params.sorting() ? $filter('orderBy')(filteredData, params.orderBy()) : filteredData;
          params.total(orderedData.length);
          $scope.noRecords = (orderedData.length) == 0 ? true : false;
          $scope.resetCheckboxes();
          return orderedData.slice((params.page() - 1) * params.count(), params.page() * params.count());
        }
      });
      $scope.loading = false;
    });

    // Node bulk operation.
    $scope.nodeBulkOperation = function(operation) {
      var nids = [];
      angular.forEach($scope.selectedItems, function(selectedItem, nid) {
        if (selectedItem) {
          nids.push(parseInt(nid));
        }
      });
      // Bulk operation DELETE should go through Undo option.
      if (operation == 'delete') {
        $scope.nodeDelete(nids);
      } else {
        $scope.changeStatus(nids, operation);
      }
    }

    $scope.changeStatus = function(nid, publish_status) {
      var nids = angular.isArray(nid) ? nid : [nid];
      var operationName = (publish_status) ? 'published' : 'unpublished';
      buttonSpinnerStatus.SetState(operationName, true);
      nodeService.bulk(operationName, nids, {
        details: false,
        operation: false
      }).then(function(response) {
        if (response.data.data.saved) {
          buttonSpinnerStatus.SetState(operationName, false);
          $scope.message = 'Selected content has been ' + operationName + '.';
          angular.forEach($scope.tableParams.data, function(node, key) {
            if (nids.indexOf(node.id) > -1) {
              if (publish_status) {
                $scope.tableParams.data[key].publish_status = true;
              } else {
                $scope.tableParams.data[key].publish_status = false;
              }
            }
          });
        } else {
          buttonSpinnerStatus.SetState(operationName, false);
          $scope.message = messageFailed;
        }
      });
    };

    // Show Undo div to user for 8 seconds on delete.
    $scope.deleteUndoAction = true;
    $scope.deleteUndoMessage = true;
    var nodeId, timer, list;
    var oldList = [];
    var newDataList = [];
    $scope.nodeDelete = function(nid) {
      nodeId = angular.isArray(nid) ? nid : [nid];
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
        $scope.deleteNodeOnClose();
      }, 8000);
    };

    $scope.deleteNodeOnClose = function() {
      $timeout.cancel(timer);
      $scope.message = 'Selected content has been deleted.';
      $scope.deleteUndoAction = true;
      nodeService.bulk('delete', nodeId, {
        details: false,
        operation: false
      }).then(function(response) {
        if (response.data.data.deleted) {
          $scope.message = 'Selected content has been deleted.';
          $scope.deleteUndoAction = true;
        } else {
          $scope.message = messageFailed;
        }
      });
    };

    $scope.deleteUndo = function() {
      $timeout.cancel(timer);
      $scope.deleteUndoAction = true;
      $scope.deleteUndoMessage = !$scope.deleteUndoMessage;
      timer = $timeout(function() {
        $scope.deleteUndoMessage = true;
      }, 2000);
      $scope.tableParams.data = oldList;
    };

    $scope.deleteUndoMessageClose = function() {
      $scope.deleteUndoMessage = true;
    };

    // Initialize apply taxonomy term dropdown.
    $scope.applyTermModel = [];
    $scope.applyTermSettings = {
      scrollable: true,
      termDropdown: true,
      termOperation: true,
      buttonClasses: '',
      idProp: 'value'
    };
    // Initialize remove taxonomy term dropdown.
    $scope.removeTermModel = [];
    $scope.removeTermSettings = {
      scrollable: true,
      termDropdown: true,
      termOperation: true,
      buttonClasses: '',
      idProp: 'value'
    };
    // Initialize content type filter.
    $scope.selectAllFlag = false;
    $rootScope.$watch('selectAllFlag', function(newValue) {
      $scope.selectAllFlag = newValue;
    });
    $scope.contentTypeModel = [];
    $scope.contentTypeSettings = {
      scrollable: true,
      smartButtonMaxItems: 2,
      showAllConentTypeCheckBox: true,
      selectAllDefault: true
    };
    $scope.contentTypeTexts = {
      buttonDefaultText: 'All content types',
    }
    if (angular.isDefined(Drupal.settings.cpContent.contentTypeOptions)) {
      $scope.contentTypes = Drupal.settings.cpContent.contentTypeOptions;
    }
    // Initialize taxonomy term filter.
    $scope.taxonomyTermsModel = [];
    $scope.taxonomyTermsSettings = {
      scrollable: true,
      smartButtonMaxItems: 2,
      termDropdown: true,
      termOperation: false,
      idProp: 'value'
    };
    $scope.taxonomyTermsTexts = {
      buttonDefaultText: 'Taxonomy Terms'
    };
    // Node edit and delete popover.
    $scope.showPopover = false;
    $scope.popOver = function($event, nid) {
      if (angular.isDefined(nid)) {
        $scope.showPopover = nid;
        $event.stopPropagation();
        $event.preventDefault();
      }
    };
    // Hide popover.
    window.onclick = function() {
      if ($scope.showPopover) {
        $scope.showPopover = false;
        $scope.$apply();
      }
    };

    // Bulk operation checkboxes.
    $scope.checkboxes = {
      'checked': false,
      items: {}
    };
    $scope.disableBulkOptions = true;
    // Watch for check all checkbox.
    $scope.$watch('checkboxes.checked', function(value) {
      if (angular.isDefined($scope.tableParams)) {
        angular.forEach($scope.tableParams.data, function(node) {
          if (angular.isDefined(node.id)) {
            $scope.checkboxes.items[node.id] = value;
          }
        });
      }
      $scope.disableBulkOptions = !value;
      $scope.selectedItems = $scope.checkboxes.items;
    });
    // Watch for data checkboxes.
    $scope.$watch('checkboxes.items', function(values) {
      if (angular.isDefined($scope.tableParams)) {
        if (!$scope.tableParams.data) {
          return;
        }
        var checked = 0,
          unchecked = 0,
          total = $scope.tableParams.data.length;
        angular.forEach($scope.tableParams.data, function(node) {
          checked += ($scope.checkboxes.items[node.id]) || 0;
          unchecked += (!$scope.checkboxes.items[node.id]) || 0;
        });
        if ((unchecked == 0) || (checked == 0)) {
          $scope.checkboxes.checked = (checked == total);
        }
        if (checked > 0) {
          $scope.disableBulkOptions = false;
        } else {
          $scope.disableBulkOptions = true;
          $scope.checkboxes.checked = false;
        }
        // Grayed checkbox.
        angular.element(document.getElementById("select_all")).prop("indeterminate", (checked != 0 && unchecked != 0));
      }

    }, true);

    $scope.closeMessage = function() {
      $scope.message = false;
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
        angular.forEach($scope.contentTypeModel, function(type, key) {
          selectedType.push(type.id);
        });
        filter.type = selectedType;
        $scope.tableParams.filter(filter);
      }
      if ($scope.taxonomyTermsModel.length > 0) {
        var selectedTerms = [];
        angular.forEach($scope.taxonomyTermsModel, function(term, key) {
          selectedTerms.push(parseInt(term.id));
        });
        filter.og_vocabulary = selectedTerms;
        $scope.tableParams.filter(filter);
      }
      $scope.tableParams.reload();
    };

    $scope.getMatchedTaxonomyTerms = function(termOperation) {
      if (termOperation) {
        var selectedNids = [];
        var selectedTypes = [];
        var macthedVocab = [];
        var results;
        angular.forEach($scope.selectedItems, function(state, nid) {
          if (state) {
            selectedNids.push(parseInt(nid));
          }
        });
        angular.forEach($scope.tableParams.data, function(node, key) {
          if (selectedNids.indexOf(node.id) > -1) {
            selectedTypes.push(node.type);
          }
        });
        selectedTypes = selectedTypes.filter(function(value, index) {
          return selectedTypes.indexOf(value) == index
        });
        return vocabService.fetch().then(function(ogVocabTerms) {
          angular.forEach(ogVocabTerms, function(vocab, key) {
            vocab.bundles.node.sort()
            var ret = [];
            for (var i = 0; i < selectedTypes.length; i += 1) {
              if (vocab.bundles.node.indexOf(selectedTypes[i]) > -1) {
                ret.push(selectedTypes[i]);
              }
            }
            if (selectedTypes.length == ret.length) {
              macthedVocab.push(vocab);
            }
          });
          if (macthedVocab.length == 0) {
            var contentTypesText = (selectedTypes.length > 0) ? selectedTypes.join(', ') : selectedTypes[0];
            contentTypesText = $filter('removeUnderscore')(contentTypesText);
            if (selectedTypes.length == 1) {
              results = {
                error: 'No vocabularies enabled for ' + contentTypesText + ' content type.',
                vocab: macthedVocab
              };
            } else {
              results = {
                error: contentTypesText + " do not share the same vocabularies.",
                vocab: macthedVocab
              };
            }
          } else {
            results = {
              error: false,
              vocab: macthedVocab
            };
          }
          return results;
        });

      } else {
        return vocabService.fetch().then(function(ogVocabTerms) {
          if (ogVocabTerms.length == 0) {
            results = {
              error: "No vocabularies available.",
              vocab: ogVocabTerms
            };
          } else {
            results = {
              error: false,
              vocab: ogVocabTerms
            };
          }
          return results;
        });
      }
    }

    $scope.nodeTermOperation = function(operation) {
      var nids = [];
      var tids = [];
      var termModel = (operation == 'applyTerm') ? $scope.applyTermModel : $scope.removeTermModel;
      angular.forEach($scope.selectedItems, function(value, key) {
        if (value) {
          nids.push(parseInt(key));
        }
      });
      angular.forEach(termModel, function(obj, key) {
        tids.push(parseInt(obj.id));
      });
      if (operation == 'applyTerm') {
        return nodeService.bulk('applyTerm', nids, {
          tids: tids,
          operation: false
        }).then(function(response) {
          if (response.data.data.saved) {
            $scope.message = 'Terms have been applied to selected content.';
            angular.forEach($scope.tableParams.data, function(node, key) {
              if (nids.indexOf(node.id) > -1) {
                var newTerms = [];
                if (node.og_vocabulary == null) {
                  node.og_vocabulary = [];
                }
                if (node.og_vocabulary.length > 0) {
                  angular.forEach(node.og_vocabulary, function(vocab) {
                    if (tids.indexOf(parseInt(vocab.tid)) == -1) {
                      angular.forEach(tids, function(tid) {
                        newTerms.push({
                          tid: tid
                        });
                      });
                    }
                  });
                } else {
                  angular.forEach(tids, function(tid) {
                    newTerms.push({
                      tid: tid
                    });
                  });
                }
                if (newTerms.length > 0) {
                  angular.forEach(newTerms, function(term) {
                    node.og_vocabulary.push(term);
                  });
                }
              }

            });

            return true;
          } else {
            $scope.message = messageFailed;
            return false;
          }
        }, function(error) {
          $scope.message = error.data.title;
          return false;
        });

      } else {
        return nodeService.bulk('removeTerm', nids, {
          tids: tids,
          operation: false
        }).then(function(response) {
          if (response.data.data.saved) {
            $scope.message = 'Terms have been removed from selected content.';
            angular.forEach($scope.tableParams.data, function(node, key) {
              if (nids.indexOf(node.id) > -1) {
                if (angular.isDefined($scope.tableParams.data[key].og_vocabulary)) {
                  angular.forEach($scope.tableParams.data[key].og_vocabulary, function(vocab, term_key) {
                    if (tids.indexOf(parseInt(vocab.tid)) > -1) {
                      $scope.tableParams.data[key].og_vocabulary[term_key].tid = null;
                    }
                  });
                }
              }
            });
            return true;
          } else {
            $scope.message = messageFailed;
            return false;
          }
        }, function(error) {
          $scope.message = error.data.title;
          return false;
        });
      }
    }

    $scope.setMessage = function(message) {
      $scope.message = message;
    }

    $scope.close = function(arg) {
      window.location.reload();
    }

  }]);

  /**
   * Fetching cp content and fill it in setting form modal.
   */
  m.directive('cpContent', function() {
    return {
      templateUrl: function() {
        return Drupal.settings.paths.cpContent + '/cp_content.html'
      },
    };
  });

  m.directive('cpContentDropdownMultiselect', ['$rootScope', '$filter', '$document', '$compile', '$parse', 'EntityService', 'buttonSpinnerStatus',

    function($rootScope, $filter, $document, $compile, $parse, EntityService, bss) {

      return {
        restrict: 'AE',
        scope: {
          selectedModel: '=',
          options: '=',
          extraSettings: '=',
          bulkAccess: '=',
          events: '=',
          translationTexts: '=',
          groupBy: '@',
          getMatchedTaxonomyTerms: '&',
          nodeTermOperation: '&',
          setMessage: '&'
        },
        templateUrl: function() {
          return Drupal.settings.paths.cpContent + '/cp_content_dropdown.html'
        },
        link: function(scope, element, attrs) {
          scope.$watch('bulkAccess', function(newValue) {
            scope.disableBulkOptions = newValue;
          });

          scope.checkboxes = attrs.checkboxes ? true : false;
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

          scope.disableApply = true;

          scope.toggleDropdown = function() {
            scope.open = !scope.open;
            if (scope.settings.termDropdown) {
              scope.getMatchedTaxonomyTerms({
                state: scope.settings.termOperation
              }).then(function(result) {
                if (!result.error) {
                  scope.options = result.vocab;
                  scope.showTermErrorMessage = false;
                } else {
                  scope.termErrorMessage = result.error;
                  scope.showTermErrorMessage = true;
                }
              });
            }
          };

          // Add term to vocabulary.
          var termService = new EntityService('taxonomy', 'id');
          scope.addTerm = function(key, vocabName, vocabMachineName) {
            if (angular.isDefined(scope.orderedItems[key].termName)) {
              bss.SetState('add_term_form', true);
              var termName = scope.orderedItems[key].termName;
              termService.add({
                vocab: vocabMachineName,
                label: termName
              }).then(function(response) {
                if (response.data.data[0]) {
                  scope.orderedItems[key].tree.splice(key, 0, {
                    value: response.data.data[0].id,
                    label: termName
                  });
                  scope.orderedItems[key].termName = '';
                  bss.SetState('add_term_form', false);
                  scope.setMessage({
                    message: termName + ' term have been added to ' + vocabName + ' vocabulary.'
                  });
                }
              }, function(error) {
                scope.setMessage({
                  message: error.data.title
                });
                bss.SetState('add_term_form', false);
              });

            }
          };

          // Remove selected terms from selected nodes.
          scope.removeTerms = function() {
            bss.SetState('term_form', true);
            scope.nodeTermOperation({
              termOperation: 'removeTerm'
            }).then(function(state) {
              if (state) {
                scope.open = false;
                bss.SetState('term_form', false);
              } else {
                bss.SetState('term_form', false);
              }
            });
          };

          // Add selected terms to selected nodes.
          scope.applyTerms = function() {
            bss.SetState('term_form', true);
            scope.nodeTermOperation({
              termOperation: 'applyTerm'
            }).then(function(state) {
              if (state) {
                scope.open = false;
                bss.SetState('term_form', false);
              } else {
                bss.SetState('term_form', false);
              }
            });
          };

          scope.closeTermDropdown = function() {
            scope.open = !scope.open;
          }

          if (angular.isDefined(scope.settings.groupBy)) {
            scope.$watch('options', function(newValue) {
              if (angular.isDefined(newValue)) {
                scope.orderedItems = newValue;
              }
            });
          }
          angular.extend(scope.settings, scope.extraSettings || []);
          angular.extend(scope.externalEvents, scope.events || []);
          angular.extend(scope.texts, scope.translationTexts);

          scope.stopBubbling = function($event) {
            $event.stopImmediatePropagation();
          };

          scope.groupVocabName = false;
          scope.groupToggleDropdown = function(vocabName) {
            if (scope.groupVocabName == vocabName) {
              scope.groupVocabName = false;
            } else {
              scope.groupVocabName = vocabName;
            }

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
                  if (angular.isDefined(optionItem.tree)) {
                    angular.forEach(optionItem.tree, function(optionItem) {
                      if (scope.isChecked(scope.getPropertyForObject(optionItem, scope.settings.idProp))) {
                        var displayText = scope.getPropertyForObject(optionItem, scope.settings.displayProp);
                        var converterResponse = scope.settings.smartButtonTextConverter(displayText, optionItem);
                        itemsText.push(converterResponse ? converterResponse : displayText);
                      }
                    });
                  } else if (scope.isChecked(scope.getPropertyForObject(optionItem, scope.settings.idProp))) {
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
              if (angular.isDefined(value.tree)) {
                angular.forEach(value.tree, function(value) {
                  scope.setSelectedItem(value[scope.settings.idProp], true);
                });
              } else {
                scope.setSelectedItem(value[scope.settings.idProp], true);
              }
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
            scope.disableApply = scope.selectedModel.length > 0 ? false : true;

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

  m.directive('dynamicAttr', ['$compile', function($compile) {
    return {
      restrict: 'A',
      link: function(scope, element, attrs) {
        var copy = element.clone();
        copy.attr(attrs.dynamicAttr, '');
        copy.removeAttr('dynamic-attr');
        element.replaceWith(copy);
        copy = $compile(copy)(scope);
      }
    };
  }]);

  m.filter('removeUnderscore', [function() {
    return function(string) {
      if (!angular.isString(string)) {
        return string;
      }
      return string.replace(/_/g, ' ');
    };
  }]);

  m.filter('unique', [function() {
    return function(collection, keyname) {
      var output = [],
        keys = [];
      angular.forEach(collection, function(item) {
        var key = item[keyname];
        if (keys.indexOf(key) === -1) {
          keys.push(key);
          output.push(item);
        }
      });
      return output;
    };
  }]);

})();
