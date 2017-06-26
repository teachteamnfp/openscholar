(function() {
  var m = angular.module('CpContent', ['ui.bootstrap', 'ngTable']);

  /**
   * Fetch filter options.
   */
  m.service('cpFetchFilterOptions', ['$http', function($http) {
    var service = {
      getData: function(endpoint) {
        var baseUrl = Drupal.settings.paths.api;
        // Fetch the vsite id.
        if (Drupal.settings.spaces != undefined) {
          if (Drupal.settings.spaces.id) {
            var vsite = Drupal.settings.spaces.id;
          }
        }
        var params = {
          vsite: vsite
        };
        var config = {
          params: params
        }
        var promise = $http.get(baseUrl + '/' + endpoint, config)
          .success(function(response) {});
        return promise.then(function(result) {
          return result;
        });
      }
    }

    return service;

  }]);

  /**
   * Fetch content list.
   */
  m.service('cpFetchContent', ['$http', function($http) {

    var service = {
      getData: function(page, count, sorting, filter, vsite) {
        var baseUrl = Drupal.settings.paths.api;
        var params = {
          page: page,
          vsite: vsite,
          range: count,
          sort: sorting,
        };
        var config = {
          params: params
        }
        var promise = $http.get(baseUrl + '/nodes?' + filter, config)
          .success(function(response) {});
        return promise.then(function(result) {
          return result;
        });
      }
    }

    return service;

  }]);

  /**
   * Post bulk operations such apply terms, add term.
   */
  m.service('cpBulkOperation', ['$http', function($http) {
    var service = {
      postData: function(endpoint, data, config) {
        var baseUrl = Drupal.settings.paths.api;
        var promise = $http.post(baseUrl + '/' + endpoint, data, config)
          .success(function(response) {});
        return promise.then(function(result) {
          return result;
        });
      }
    }

    return service;

  }]);

  /**
   * Fetching cp content and fill it in setting form modal.
   */
  m.directive('cpContent', ['$rootScope', '$timeout', 'NgTableParams', 'cpFetchContent', 'cpFetchFilterOptions', 'cpBulkOperation', function($rootScope, $timeout, NgTableParams, cpFetchContent, cpFetchFilterOptions, cpBulkOperation) {
    function link(scope, element, attrs) {
      scope.message = false;
      // Fetch the vsite id.
      if (Drupal.settings.spaces != undefined) {
        if (Drupal.settings.spaces.id) {
          var vsite = Drupal.settings.spaces.id;
        }
      }
      // Hide Defaut buttons from ApSettings Modal form.
      scope.$parent.$parent.$parent.showSaveButton = false;
      var tableData = function(filter) {
        scope.tableParams = new NgTableParams({
          page: 1,
          count: 10,
          sorting: {
            created: 'desc'
          }
        }, {
          total: 0,
          counts: [], //hide page counts control.
          getData: function(params) {
            $rootScope.resetCheckboxes();
            var orderBycolumn = params.orderBy();
            var sortNameValue = orderBycolumn[0].replace(/\+/g, "");
            return cpFetchContent.getData(params.page(), params.count(), sortNameValue, filter, vsite).then(function(responce) {
              params.total(responce.data.count);
              scope.noRecords = responce.data.data.length == 0 ? true : false;
              return responce.data.data;
            });
          }
        });
      };
      // Get default content.
      tableData();

      // Bulk Operation.
      scope.checkboxes = { 'checked': false, items: {} };
      $rootScope.disableApply = true;

      // Reset select all checkboxes.
      $rootScope.resetCheckboxes = function() {
        scope.checkboxes.checked = false;
        angular.forEach(scope.checkboxes.items, function(value, key) {
          scope.checkboxes.items[key] = false;
        });
      }

      // Watch for check all checkbox.
      scope.$watch('checkboxes.checked', function(value) {
        angular.forEach(scope.tableParams.data, function(node) {
          if (angular.isDefined(node.id)) {
            scope.checkboxes.items[node.id] = value;
          }
        });
        $rootScope.disableApply = !value;
        $rootScope.selectedItems = scope.checkboxes.items;
      });

      // Watch for data checkboxes.
      scope.$watch('checkboxes.items', function(values) {
        if (!scope.tableParams.data) {
            return;
        }
        var checked = 0, unchecked = 0,
          total = scope.tableParams.data.length;
        angular.forEach(scope.tableParams.data, function(node) {
          checked   +=  (scope.checkboxes.items[node.id]) || 0;
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
      }, true);

      // Bulk node Unpublish.
      scope.nodeBulkOperation = function(operation) {
        var nids = [];
        angular.forEach($rootScope.selectedItems, function(value, key) {
          if (value) {
            nids.push(key);
          }
        });
        var data = {
          nids : nids,
          operation : operation
        }
        return cpBulkOperation.postData('nodes/bulk', data).then(function(responce) {
          if (responce.data.data.saved) {
            scope.message = 'Selected content has been ' +operation+ '.';
            $rootScope.resetCheckboxes();
            tableData();
          } else {
            scope.message = 'Please select at least one item.';
          }
        });
      }

      // Initialize apply taxonomy term dropdown.
      scope.applyTermModel = [];
      $rootScope.applyTermModel = scope.applyTermModel;
      scope.applyTermSettings = {
        scrollable: true,
        smartButtonMaxItems: 2,
        termDropdown: true,
        buttonClasses: ''
      };

      // Initialize remove taxonomy term dropdown.
      scope.removeTermModel = [];
      $rootScope.removeTermModel = scope.removeTermModel;
      scope.removeTermSettings = {
        scrollable: true,
        smartButtonMaxItems: 2,
        termDropdown: true,
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
      cpFetchFilterOptions.getData('content_types').then(function(responce) {
        scope.contentTypes = responce.data.data;
      });

      scope.selectAllFlag = false;
      $rootScope.$watch('selectAllFlag', function(newValue) {
        scope.selectAllFlag = newValue;
      });

      // Initialize taxonomy term filter.
      scope.taxonomyTermsModel = [];
      scope.taxonomyTermsSettings = {
        scrollable: true,
        smartButtonMaxItems: 2,
        termDropdown: true
      };
      scope.taxonomyTermsTexts = {
        buttonDefaultText: 'Taxonomy Terms'
      };
      cpFetchFilterOptions.getData('taxonomy').then(function(responce) {
        scope.taxonomyTermsOptions = responce.data.data;
      });

      // Search button: Filter data by title, content-type, taxonomy.
      scope.search = function() {
        scope.message = false;
        var filter = '';
        if (scope.label) {
          filter += "filter[label][value]=" + scope.label + "&filter[label][operator]=CONTAINS&";
        }
        if (scope.contentTypeModel.length > 0) {
          angular.forEach(scope.contentTypeModel, function(value, key) {
            filter += "filter[type][value][" + key + "]=" + scope.contentTypeModel[key].id + "&filter[type][operator][" + key + "]=IN&";
          });
        }
        if (scope.taxonomyTermsModel.length > 0) {
          angular.forEach(scope.taxonomyTermsModel, function(value, key) {
            filter += "filter[og_vocabulary][value][" + key + "]=" + scope.taxonomyTermsModel[key].id + "&filter[og_vocabulary][operator][" + key + "]=IN&";
          });
        }
        // Get filtered content.
        tableData(filter);
      };

      // Edit and Delete by nid.
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

      scope.deleteUndoAction = true;
      scope.deleteUndoMessage = true;
      var nodeId, timer;
      scope.nodeDelete = function(nid) {
        scope.deleteUndoMessage = true;
        scope.deleteUndoAction = !scope.deleteUndoAction;
        nodeId = nid;
        timer = $timeout(function () {
          scope.deleteUndoAction = !scope.deleteUndoAction;
          //@todo
          console.log(nodeId);
        }, 3000);
      };

      scope.deleteNodeOnClose = function() {
        //@todo
        console.log(nodeId);
      };

      scope.deleteUndo = function() {
        $timeout.cancel(timer);
        scope.deleteUndoAction = true;
        scope.deleteUndoMessage = !scope.deleteUndoMessage;
      };

      scope.deleteUndoMessageClose = function() {
        scope.deleteUndoMessage = true;
      };

      scope.nodeEdit = function(nid) {
        //@todo
        console.log(nid);
      };
    }

    return {
      link: link,
      templateUrl: function() {
        return Drupal.settings.paths.cpContent + '/cp_content.html'
      },
    };
  }]);

  m.directive('cpContentDropdownMultiselect', ['$rootScope', '$filter', '$document', '$compile', '$parse', 'cpBulkOperation', 'cpFetchFilterOptions',

    function($rootScope, $filter, $document, $compile, $parse, cpBulkOperation, cpFetchFilterOptions) {

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
              cpFetchFilterOptions.getData('taxonomy').then(function(responce) {
                scope.options = responce.data.data;
              });
            }
          };

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

          scope.groupToggleDropdown = function(arr, index) {
            if (arr.length > 0) {
              var vocab = arr[index].vocab;
              if (!scope[vocab]) {
                angular.forEach(arr, function(val, key) {
                  var atp = arr[key].vocab;
                  if (key == 0) {
                    scope[atp] = true;
                  } else {
                    scope[atp] = false;
                  }
                });
              }
              if (!angular.isDefined(scope[vocab])) {
                scope[vocab] = true;
              } else {
                scope[vocab] = !scope[vocab];
              }
            }
          };

          scope.checkboxClick = function($event, id) {
            scope.setSelectedItem(id);
            $event.stopImmediatePropagation();
          };

          // Add term to vocabulary.
          scope.addTerm = function(key, vid, vocab) {
            if (angular.isDefined(scope.orderedItems[key].termName)) {
              var data = {
                vid: vid,
                name: scope.orderedItems[key].termName
              }
              return cpBulkOperation.postData('nodes/term/add', data).then(function(responce) {
                if (responce.data.data.term_id) {
                  scope.orderedItems.splice(key, 0, {id: responce.data.data.term_id, label: data.name, vid: data.vid, vocab: vocab});
                  scope.orderedItems[key].termName = '';
                  scope.$parent.$parent.message = data.name+' term have been added to '+vocab+' vocabulary.';
                } else {
                  scope.$parent.$parent.message = message.failedMessage;
                }
              });
            }
          };

          scope.removeTerms = function() {
            var nids = [];
            angular.forEach($rootScope.selectedItems, function(value, key) {
              if (value) {
                nids.push(key);
              }
            });
            var terms = [];
            angular.forEach($rootScope.removeTermModel, function(obj, key) {
              terms.push(obj.id);
            });
            var data = {
              nids: nids,
              terms: terms
            };
            return cpBulkOperation.postData('nodes/bulk/term/remove', data).then(function(responce) {
              if (responce.data.data.saved) {
                scope.$parent.$parent.message = 'Terms have been removed from selected content.';
                scope.open = false;
                $rootScope.resetCheckboxes();
              } else {
                scope.$parent.$parent.message = 'Please select a term to be removed.';
              }
            });
          };

          scope.applyTerms = function() {
            var nids = [];
            angular.forEach($rootScope.selectedItems, function(value, key) {
              if (value) {
                nids.push(key);
              }
            });
            var terms = [];
            angular.forEach($rootScope.applyTermModel, function(obj, key) {
              terms.push(obj.id);
            });
            var data = {
              nids: nids,
              terms: terms
            };
            return cpBulkOperation.postData('nodes/bulk/term/apply', data).then(function(responce) {
              if (responce.data.data.saved) {
                scope.$parent.$parent.message = 'Terms have been applied to selected content.';
                scope.open = false;
                $rootScope.resetCheckboxes();
              } else {
                scope.$parent.$parent.message = 'Please select a term to be applied.';
              }
            });
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

})();
