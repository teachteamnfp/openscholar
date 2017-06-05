(function() {
  var m = angular.module('CpContent', ['ui.bootstrap', 'ngTable']);

  /**
   * Fetch content types.
   */
  m.service('cpFetchContentTypes', ['$http', function($http) {
    var service = {
      getData: function() {
        var baseUrl = Drupal.settings.basePath;
        var params = {};
        var config = {
          params: params
        }
        var promise = $http.get(baseUrl + 'cp/content_types', config)
          .success(function(response) {});
        return promise.then(function(result) {
          return result;
        });
      }
    }

    return service;

  }]);

  /**
   * Fetch taxonomy terms.
   */
  m.service('cpFetchTaxonomyTerms', ['$http', function($http) {
    var service = {
      getData: function() {
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
        var promise = $http.get(baseUrl + '/taxonomy', config)
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
   * Fetching cp content and fill it in setting form modal.
   */
  m.directive('cpContent', ['NgTableParams', 'cpFetchContent', 'cpFetchContentTypes', 'cpFetchTaxonomyTerms', function(NgTableParams, cpFetchContent, cpFetchContentTypes, cpFetchTaxonomyTerms) {
    function link($scope, $element, $attrs) {
      // Fetch the vsite id.
      if (Drupal.settings.spaces != undefined) {
        if (Drupal.settings.spaces.id) {
          var vsite = Drupal.settings.spaces.id;
        }
      }
      // Hide Defaut buttons from ApSettings Modal form.
      $scope.$parent.$parent.$parent.showSaveButton = false;
      var tableData = function(filter) {
        $scope.tableParams = new NgTableParams({
          page: 1,
          count: 10,
          sorting: {
            changed: 'desc'
          }
        }, {
          total: 0,
          counts: [], //hide page counts control.
          getData: function(params) {
            var orderBycolumn = params.orderBy();
            var sortNameValue = orderBycolumn[0].replace(/\+/g, "");
            return cpFetchContent.getData(params.page(), params.count(), sortNameValue, filter, vsite).then(function(responce) {
              params.total(responce.data.count);
              return responce.data.data;
            });
          }
        });
        // Bulk Operation.
        $scope.checkboxes = {
          checked: false,
          items: {}
        };

      }

      // Initialize content type dropdown.
      $scope.contentTypeModel = [];
      $scope.contentTypeSettings = {
        scrollable: true,
        smartButtonMaxItems: 2,
        showCheckAll: true,
        showUncheckAll: true
      };
      $scope.contentTypeTexts = {
        buttonDefaultText: 'All content type',
        checkAll: 'Check all content type',
        uncheckAll: 'Uncheck all content type',
      }
      cpFetchContentTypes.getData().then(function(responce) {
        $scope.contentTypes = responce.data.data;
      });

      // Initialize taxonomy term dropdown.
      $scope.taxonomyTermsModel = [];
      $scope.taxonomyTermsSettings = {
        scrollable: true,
        smartButtonMaxItems: 2,
        showCheckAll: false,
        showUncheckAll: false
      };
      $scope.taxonomyTermsTexts = {
        buttonDefaultText: 'Taxonomy Terms'
      };
      cpFetchTaxonomyTerms.getData().then(function(responce) {
        $scope.taxonomyTerms = responce.data.data;
      });



      // Get default content.
      tableData();
      // Search button: Filter data by title, content-type, taxonomy.
      $scope.search = function() {
        var filter = '';
        if ($scope.label) {
          filter += "filter[label][value]=" + $scope.label + "&filter[label][operator]=CONTAINS&";
        }
        if ($scope.contentTypeModel.length > 0) {
          angular.forEach($scope.contentTypeModel, function(value, key) {
            filter += "filter[type][value]["+key+"]=" + $scope.contentTypeModel[key].id + "&filter[type][operator]["+key+"]=IN&";
          });
        }
        if ($scope.taxonomyTermsModel.length > 0) {
          angular.forEach($scope.taxonomyTermsModel, function(value, key) {
            filter += "filter[og_vocabulary][value]["+key+"]=" + $scope.taxonomyTermsModel[key].id + "&filter[og_vocabulary][operator]["+key+"]=IN";
          });
        }
        console.log(filter);
        // Get filtered content.
        tableData(filter);
      };

    }

    return {
      link: link,
      templateUrl: function() {
        return Drupal.settings.paths.cpContent + '/cp_content.html'
      },
    };
  }]);

  m.directive('ngDropdownMultiselect', ['$filter', '$document', '$compile', '$parse',

    function($filter, $document, $compile, $parse) {

      return {
        restrict: 'AE',
        scope: {
          selectedModel: '=',
          options: '=',
          extraSettings: '=',
          events: '=',
          searchFilter: '=?',
          translationTexts: '=',
          groupBy: '@'
        },
        templateUrl: function() {
          return Drupal.settings.paths.cpContent + '/cp_content_dropdown.html'
        },
        link: function($scope, $element, $attrs) {

          $scope.checkboxes = $attrs.checkboxes ? true : false;
          $scope.groups = $attrs.groupBy ? true : false;

          var $dropdownTrigger = $element.children()[0];

          $scope.toggleDropdown = function() {
            $scope.open = !$scope.open;
          };

          $scope.groupToggleDropdown = function(arr,index) {
            var vocab = arr[index].vocab;
            if (!$scope[vocab]) {
              angular.forEach(arr,function(val, key){
                var atp = arr[key].vocab;
                $scope[atp] = false;
              });
            }
            if (!angular.isDefined($scope[vocab])) {
              $scope[vocab] = true;
            }else {
              $scope[vocab] = !$scope[vocab];

            }
          };

          $scope.checkboxClick = function($event, id) {
            $scope.setSelectedItem(id);
            $event.stopImmediatePropagation();
          };

          $scope.externalEvents = {
            onItemSelect: angular.noop,
            onItemDeselect: angular.noop,
            onSelectAll: angular.noop,
            onDeselectAll: angular.noop,
            onInitDone: angular.noop,
            onMaxSelectionReached: angular.noop
          };

          $scope.settings = {
            dynamicTitle: true,
            scrollable: false,
            scrollableHeight: '300px',
            closeOnBlur: true,
            displayProp: 'label',
            idProp: 'id',
            externalIdProp: 'id',
            enableSearch: false,
            selectionLimit: 0,
            showCheckAll: true,
            showUncheckAll: true,
            closeOnSelect: false,
            buttonClasses: 'btn btn-default',
            closeOnDeselect: false,
            groupBy: $attrs.groupBy || undefined,
            groupByTextProvider: null,
            smartButtonMaxItems: 0,
            smartButtonTextConverter: angular.noop
          };

          $scope.texts = {
            checkAll: 'Check All',
            uncheckAll: 'Uncheck All',
            selectionCount: 'checked',
            selectionOf: '/',
            searchPlaceholder: 'Search...',
            buttonDefaultText: 'Select',
            dynamicButtonTextSuffix: 'checked'
          };

          $scope.searchFilter = $scope.searchFilter || '';

          if (angular.isDefined($scope.settings.groupBy)) {
            $scope.$watch('options', function(newValue) {
              if (angular.isDefined(newValue)) {
                $scope.orderedItems = $filter('orderBy')(newValue, $scope.settings.groupBy);
              }
            });
          }

          angular.extend($scope.settings, $scope.extraSettings || []);
          angular.extend($scope.externalEvents, $scope.events || []);
          angular.extend($scope.texts, $scope.translationTexts);

          $scope.singleSelection = $scope.settings.selectionLimit === 1;

          function getFindObj(id) {
            var findObj = {};

            if ($scope.settings.externalIdProp === '') {
              findObj[$scope.settings.idProp] = id;
            } else {
              findObj[$scope.settings.externalIdProp] = id;
            }

            return findObj;
          }

          function clearObject(object) {
            for (var prop in object) {
              delete object[prop];
            }
          }

          if ($scope.singleSelection) {
            if (angular.isArray($scope.selectedModel) && $scope.selectedModel.length === 0) {
              clearObject($scope.selectedModel);
            }
          }

          if ($scope.settings.closeOnBlur) {
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
                $scope.$apply(function() {
                  $scope.open = false;
                });
              }
            });
          }

          $scope.getGroupTitle = function(groupValue) {
            if ($scope.settings.groupByTextProvider !== null) {
              return $scope.settings.groupByTextProvider(groupValue);
            }

            return groupValue;
          };

          $scope.getButtonText = function() {
            if ($scope.settings.dynamicTitle && ($scope.selectedModel.length > 0 || (angular.isObject($scope.selectedModel) && _.keys($scope.selectedModel).length > 0))) {
              if ($scope.settings.smartButtonMaxItems > 0) {
                var itemsText = [];

                angular.forEach($scope.options, function(optionItem) {
                  if ($scope.isChecked($scope.getPropertyForObject(optionItem, $scope.settings.idProp))) {
                    var displayText = $scope.getPropertyForObject(optionItem, $scope.settings.displayProp);
                    var converterResponse = $scope.settings.smartButtonTextConverter(displayText, optionItem);

                    itemsText.push(converterResponse ? converterResponse : displayText);
                  }
                });

                if ($scope.selectedModel.length > $scope.settings.smartButtonMaxItems) {
                  itemsText = itemsText.slice(0, $scope.settings.smartButtonMaxItems);
                  itemsText.push('...');
                }

                return itemsText.join(', ');
              } else {
                var totalSelected;

                if ($scope.singleSelection) {
                  totalSelected = ($scope.selectedModel !== null && angular.isDefined($scope.selectedModel[$scope.settings.idProp])) ? 1 : 0;
                } else {
                  totalSelected = angular.isDefined($scope.selectedModel) ? $scope.selectedModel.length : 0;
                }

                if (totalSelected === 0) {
                  return $scope.texts.buttonDefaultText;
                } else {
                  return totalSelected + ' ' + $scope.texts.dynamicButtonTextSuffix;
                }
              }
            } else {
              return $scope.texts.buttonDefaultText;
            }
          };

          $scope.getPropertyForObject = function(object, property) {
            if (angular.isDefined(object) && object.hasOwnProperty(property)) {
              return object[property];
            }

            return '';
          };

          $scope.selectAll = function() {
            $scope.deselectAll(false);
            $scope.externalEvents.onSelectAll();

            angular.forEach($scope.options, function(value) {
              $scope.setSelectedItem(value[$scope.settings.idProp], true);
            });
          };

          $scope.deselectAll = function(sendEvent) {
            sendEvent = sendEvent || true;

            if (sendEvent) {
              $scope.externalEvents.onDeselectAll();
            }

            if ($scope.singleSelection) {
              clearObject($scope.selectedModel);
            } else {
              $scope.selectedModel.splice(0, $scope.selectedModel.length);
            }
          };

          $scope.setSelectedItem = function(id, dontRemove) {
            var findObj = getFindObj(id);
            var finalObj = null;

            if ($scope.settings.externalIdProp === '') {
              finalObj = _.find($scope.options, findObj);
            } else {
              finalObj = findObj;
            }

            if ($scope.singleSelection) {
              clearObject($scope.selectedModel);
              angular.extend($scope.selectedModel, finalObj);
              $scope.externalEvents.onItemSelect(finalObj);
              if ($scope.settings.closeOnSelect) $scope.open = false;

              return;
            }

            dontRemove = dontRemove || false;

            var exists = _.findIndex($scope.selectedModel, findObj) !== -1;

            if (!dontRemove && exists) {
              $scope.selectedModel.splice(_.findIndex($scope.selectedModel, findObj), 1);
              $scope.externalEvents.onItemDeselect(findObj);
            }
            else if (!exists && ($scope.settings.selectionLimit === 0 || $scope.selectedModel.length < $scope.settings.selectionLimit)) {
              $scope.selectedModel.push(finalObj);
              $scope.externalEvents.onItemSelect(finalObj);
            }
            if ($scope.settings.closeOnSelect) $scope.open = false;
          };

          $scope.isChecked = function(id) {
            if ($scope.singleSelection) {
              return $scope.selectedModel !== null && angular.isDefined($scope.selectedModel[$scope.settings.idProp]) && $scope.selectedModel[$scope.settings.idProp] === getFindObj(id)[$scope.settings.idProp];
            }

            return _.findIndex($scope.selectedModel, getFindObj(id)) !== -1;
          };

          $scope.externalEvents.onInitDone();
        }
      };
    }
  ]);

})();
