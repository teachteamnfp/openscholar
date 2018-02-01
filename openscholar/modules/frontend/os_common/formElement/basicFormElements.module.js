(function () {

  var renderFieldsetElements = [function () {
    return {
      restrict: 'A',
      scope: {
        value: '=ngModel',
        element: '='
      },
      template: '<div class="form-item" ng-repeat="(key, field) in formElements">'+
      '<div form-element element="field" value="formData[key]"><span>placeholder</span></div>'+
      '</div>',
      link: function (scope, elem, attr) {
        var fieldsetElements = scope.element;
        scope.formElements = {};
        scope.formData = {};
        for (var k in fieldsetElements) {
          if (angular.isObject(fieldsetElements[k])) {
            if (fieldsetElements.name == 'options' || fieldsetElements.name == 'revision_information') {
              scope.formData[k] = fieldsetElements[k]['#default_value'] || null;
            } else {
              scope.formData[k] = (fieldsetElements[k]['#default_value']) ? fieldsetElements[k]['#default_value'][0].value : null;
            }
            var attributes = {
              name: k
            };
            for (var j in fieldsetElements[k]) {
              if (j.indexOf('#') === 0) {
                var attrs = j.substr(1, j.length);
                attributes[attrs] = fieldsetElements[k][j];
              }
            }
            scope.formElements[k] = attributes;
          }
        }
        scope.$watchCollection('formData', function(newFormData) {
          if (fieldsetElements.name == 'options') {
            var message = (newFormData.status) ? 'Published to this site' : 'Not published';
            message += (newFormData.sticky) ? ', Sticky at top of lists' : '';
            message += (newFormData.noindex) ? ', Noindex' : '';
            message = '('+message+')';
          }
          scope.value = {message: message, fields: newFormData};
        });
      }
    };
  }];

  var m = angular.module('basicFormElements', ['osHelpers', 'ngSanitize', 'ui.bootstrap']);

  /**
   * SelectOptGroup directive.
   */
  m.directive('feOptgroup', ['$sce', function ($sce) {
    return {
      scope: {
        name: '@',
        value: '=ngModel',
        element: '='
      },
      template: '<label for="{{id}}">{{title}}</label>' +
        '<div class="form-item form-type-select">' +
        '<select class="form-select" id="{{id}}" name="{{name}}" ng-model="value">' +
        '<option ng-repeat="category in categories | filterWithItems:false" value="{{category.id}}">{{category.name}}</option>' +
        '<optgroup ng-repeat="category in categories | filterWithItems:true" label="{{category.name}}">' +
        '<option ng-repeat="subCat in category.items" value="{{subCat.id}}">{{subCat.name}}</option>' +
        '</optgroup>' +
        '</select>' +
        '</div>',
      link: function (scope, elem, attr) {
        scope.id = attr['inputId'];
        scope.title = scope.element.title;
        var items = [];
        angular.forEach(scope.element.options, function(value, key) {
          if (angular.isObject(value)) {
            var data = {};        
            data.id = key;
            data.name = key;
            data.items = [];
            angular.forEach(value, function(childOption, childKey) {
              var subcat = {};
              subcat.id = childKey;
              subcat.name = childOption;
              data.items.push(subcat);
            });
            items.push(data);
          } else {
            var data = {};
            data.id = key;
            data.name = value;
            items.push(data);
          }
        });
        scope.categories = items;
      }
    }
  }]);

  /**
   * Select directive.
   */
  m.directive('feSelect', ['$sce', function ($sce) {
    return {
      scope: {
        name: '@',
        value: '=ngModel',
        element: '='
      },
      template: '<label for="{{id}}">{{title}}</label>' +
        '<div class="form-item form-type-select"><select class="form-select" id="{{id}}" name="{{name}}" ng-model="value">' +
          '<option value="">Select</option>' +
          '<option ng-repeat="(val, label) in options" value="{{val}}" ng-bind-html="label"></option>' +
        '</select></div>',
      link: function (scope, elem, attr) {
        scope.id = attr['inputId'];
        scope.options = scope.element.options;
        scope.title = scope.element.title;
      }
    }
  }]);

  /**
   * Checkboxes directive.
   */
  m.directive('feCheckboxes', ['$sce', function ($sce) {
    return {
      require: 'ngModel',
      scope: {
        name: '@',
        value: '=ngModel',
        element: '='
      },
      template: '<label for="{{id}}">{{title}}</label>' +
      '<div id="{{id}}" class="form-checkboxes">' +
        '<div ng-show="element.select_all">' +          
          '<input ng-model="selectAll" type="checkbox" class="form-checkbox" ng-disabled="element.disabled" ng-change="masterChange()">' + 
          '&nbsp;<label class="option bold">Select All</label>' +
        '</div>' +
        '<div ng-if="element.sorted_options" class="form-item form-type-checkbox" ng-repeat="option in options | orderBy: \'label\'">' +
          '<input ng-model="value[option.key]" ng-checked="value[option.key]" type="checkbox" id="{{id}}-{{option.key}}" name="{{name}}" value="{{option.key}}" class="form-checkbox" ng-disabled="element.disabled">' + 
          '&nbsp;<label class="option" for="{{id}}-{{option.key}}" ng-bind-html="option.label"></label>' +
        '</div>' +
        '<div ng-if="!element.sorted_options" class="form-item form-type-checkbox" ng-repeat="option in options">' +
          '<input ng-model="value[option.key]" ng-checked="value[option.key]" type="checkbox" id="{{id}}-{{option.key}}" name="{{name}}" value="{{option.key}}" class="form-checkbox" ng-disabled="element.disabled">' + 
          '&nbsp;<label class="option" for="{{id}}-{{option.key}}" ng-bind-html="option.label"></label>' +
        '</div>' +
      '</div> ',
      link: function (scope, elem, attr) {
        scope.id = attr['inputId'];
        scope.options = scope.element.options;       
        scope.title = scope.element.title;

        scope.masterChange = function () {
          if (scope.selectAll) {
            angular.forEach(scope.options, function (cb) {
              scope.value[cb.key] = true;
            });
          } else {   
            angular.forEach(scope.options, function (cb) {
              scope.value[cb.key] = false;
            });
          }
        };
      }
    }
  }]);

  /**
   * Checkbox directive.
   * Arguments:
   *   name - string - the name of the element as Drupal expects it
   *   value - property on parent scope
   */
  m.directive('feCheckbox', [function () {
    return {
      require: 'ngModel',
      scope: {
        name: '@',
        value: '=ngModel',
        element: '='
      },
      template: '<input type="checkbox" id="{{id}}" ng-checked="{{value}}" name="{{name}}" value="1" class="form-checkbox" ng-model="value" ng-disabled="element.disabled" ng-true-value="1" ng-false-value="0"/><label class="option" for="{{id}}">{{title}}</label>',
      link: function (scope, elem, attr, ngModelController) {
        scope.id = attr['inputId'];
        scope.title = scope.element.title;
      }
    }
  }]);

  /**
   * Textbox directive.
   */
  m.directive('feTextfield', [function () {
    return {
      scope: {
        name: '@',
        value: '=ngModel',
        element: '='
      },
      template: '<label for="{{id}}">{{title}}</label>' +
      '<input type="textfield" id="{{id}}" name="{{name}}" ng-model="value" class="form-text" ng-disabled="element.disabled">',
      link: function (scope, elem, attr) {
        scope.id = attr['inputId'];
        scope.title = scope.element.title;
      }
    }
  }]);

  /**
   * Textarea directive
   */
  m.directive('feTextarea', [function () {
    return {
      scope: {
        name: '@',
        value: '=ngModel',
        element: '='
      },
      template: '<label for="{{id}}">{{title}}</label>' +
      '<textarea id="{{id}}" name="{{name}}" ng-model="value" class="form-textarea" ng-disabled="element.disabled"></textarea>',
      link: function (scope, elem, attr) {
        scope.id = attr['inputId'];
        scope.title = scope.element.title;
      }
    }
  }]);

  /**
   * Radios directive.
   */
  m.directive('feRadios', ['$sce', function ($sce) {
    return {
      scope: {
        name: '@',
        value: '=ngModel',
        element: '='
      },
      template: '<label for="{{id}}">{{title}}</label>' +
      '<div id="{{id}}" class="form-radios">' +
        '<div class="form-item form-type-radio" ng-repeat="(val, label) in options">' +
          '<input type="radio" id="{{id}}-{{val}}" name="{{name}}" value="{{val}}" ng-model="$parent.value" class="form-radio" ng-disabled="element.disabled"><label class="option" for="{{id}}-{{val}}" ng-bind-html="label"></label>' +
        '</div>' +
      '</div> ',
      link: function (scope, elem, attr) {
        scope.id = attr['inputId'];
        scope.options = scope.element.options;
        scope.title = scope.element.title;
      }
    }
  }]);

  /**
   * Submit button directive
   *
   * This type of form element should always have some kind of handler on the server end to take care of whatever this needs to do.
   */
  m.directive('feSubmit', [function () {
    return {
      scope: {
        name: '@',
        value: '=ngModel',
        element: '='
      },
      template: '<label for="{{id}}">{{title}}<button type="submit" button-spinner="settings_form_{{name}}" spinning-text="Saving" id="{{id}}" name="{{name}}">Submit</button>',
      link: function (scope, elem, attr) {
        scope.id = attr['inputId'];
        scope.title = scope.element.title;
        if (scope.element.value) {
          scope.value = scope.element.value;
        } else {
          scope.value = 'Save';
        }
      }
    }
  }]);

  /**
   * Markup directive.
   *
   * Just markup that doesn't do anything.
   */
  m.directive('feMarkup', ['$sce', function ($sce) {
    return {
      scope: {
        name: '@',
        value: '=ngModel',
        element: '=',
      },
      template: '<div ng-bind-html="markup"></div>',
      link: function (scope, elem, attr) {
        scope.id = attr['inputId'];
        scope.markup = $sce.trustAsHtml(scope.element.markup);
        scope.title = scope.element.title;
      }
    }
  }]);

  /**
   * Container directive.
   *
   * Just markup along with a container id.
   */
  m.directive('feContainer', ['$sce', function ($sce) {
    return {
      scope: {
        name: '@',
        value: '=ngModel',
        element: '=',
      },
      template: '<div ng-bind-html="markup" id="{{cid}}"></div>',
      link: function (scope, elem, attr) {
        scope.markup = $sce.trustAsHtml(scope.element.markup);
        scope.cid = scope.element.cid;
      }
    }
  }]);

/**
   * Help directive.
   *
   * Just markup that doesn't do anything.
   */
  m.directive('feHelp', ['$timeout', '$sce', function ($timeout, $sce) {
    var gsfnCounter = 0;
    return {
      scope: {
        name: '@',
        value: '=ngModel',
        element: '=',
      },
      template: '<div class="getsat-widget" id="getsat-widget-{{counter}}-{{gsfnid}}"><span class="description"></span><span class="gsfn-loading">Loading...<img src="{{loading}}"></span></div>',
      link: function (scope, elem, attr) {
        scope.gsfnid = scope.element.gsfnId;
        scope.title = scope.element.title;
        scope.counter = gsfnCounter;
        scope.loading = scope.element.loading;
        gsfnCounter = gsfnCounter + 1;
        $timeout(function() {
          GSFN.loadWidget(scope.gsfnid, {"containerId":"getsat-widget-" + scope.counter + "-" + scope.gsfnid});
        }, 2000);
      }
    }
  }]);

  /**
   * Publication cititation js directive.
   *
   * .
   */
  m.directive('fePubjsevent', [function () {
    return {
      scope: {
        name: '@',
        value: '=ngModel',
        element: '=',
      },
      template: '<div ng-bind-html="markup"></div>',
      link: function (scope, elem, attr) {
        angular.element(document.querySelectorAll(scope.element.mouseover_element)).find('div').on(scope.element.mouseover_event, function (e) {
          e.stopPropagation();
          if (scope.element.hide_element) {
            angular.element(document.querySelectorAll(scope.element.hide_element)).hide();
          }
          if (scope.element.show_element) {
            if (scope.element.show_element == 'this.id') {
              var pop_id = angular.element(this).find('input').attr('value').replace('.','');
              angular.element(document.querySelectorAll('#' + pop_id)).show();
            } else {
              angular.element(document.querySelectorAll(scope.element.show_element)).show();
            }
          }
        });
      }
    }
  }]);

   /**
   * Os Node Title Textbox directive.
   */
  m.directive('feOsNodeTitleTextfield', ['$rootScope', '$http', '$timeout', function ($rootScope, $http, $timeout) {
    return {
      scope: {
        name: '@',
        value: '=ngModel',
        element: '='
      },
      template: '<label for="{{id}}">{{title}} <span ng-if="required" class="form-required">*</span></label>' +
      '<input type="textfield" ng-blur="getUrlAlias()" id="{{id}}" name="{{name}}" ng-class="{error: error}" ng-model="value" class="form-text" ng-disabled="element.disabled">' +
      '<div ng-if="dataLoading">Loading...</div><div ng-if="urlAlias" class="url-alias"><strong>{{linkLabelText}}:</strong> {{urlAlias}} <a id="pathauto-extra-edit-path" ng-click="editUrlAlias($event)">edit</a></div>',
      
      link: function (scope, elem, attr) {
        scope.id = attr['inputId'];
        scope.title = scope.element.title;
        scope.required = (angular.isDefined(scope.element.required)) ? scope.element.required : false;
        // Set visibility of path alias element for node type page. 
        var page_node_form = document.querySelector("div.ui-dialog-content #page_node_form");
        scope.urlAlias = false;
        var pathAutoStatus;
        scope.$on("getPathautoStatus", function (evt, data) {
          pathAutoStatus = data;
        });
        scope.getUrlAlias = function () {
          if (page_node_form && !scope.element.default_value) {
            scope.linkLabelText = 'Link URL';
            scope.dataLoading = true;
            var queryArgs = {};
            var baseUrl = Drupal.settings.paths.vsite_home;
            if (angular.isDefined(Drupal.settings.spaces)) {
              if (Drupal.settings.spaces.id) {
                queryArgs.type = 'page';
                queryArgs.gid = Drupal.settings.spaces.id;
                queryArgs.prefix = baseUrl;
                queryArgs.title = scope.value;
              }
            }
            var config = {
              params: queryArgs
            };
            if (queryArgs.title && pathAutoStatus) {
              $http.get(baseUrl+'/os/pages/alias-preview', config).then(function (res) {
                var alias = res.data.data.split('/')[1];
                $rootScope.$broadcast("getUrlAlias", {alias: alias});
                scope.dataLoading = false;
                scope.urlAlias = res.data.prefix + '/' + alias;
              });
            } else {
              scope.dataLoading = false;
            }
          }
        }

        $timeout(function() {
          if (scope.element.default_value) {
            var vsiteHome = angular.isDefined(Drupal.settings.paths.vsite_home) ? Drupal.settings.paths.vsite_home : '';
            scope.urlAlias = vsiteHome + '/' + angular.element("input[name='alias']").val();
            scope.linkLabelText = 'URL';
          }
        }, 1000)    
        var state;
        scope.editUrlAlias = function(evt) {
          state = !state;
          $rootScope.$broadcast("editUrlAliasClicked", {fieldset: 'path', collapsed: state});
          evt.stopImmediatePropagation();

        }
        // Broadcast the text field value so that other directive can use it.
        scope.$watch('value', function(newValue, oldValue) {
          $rootScope.$broadcast("getNodeTitle", {name: scope.name, value: newValue});
        })

        // Error handling.
        scope.error = false;
        scope.$on("error", function (evt, data) {
          if (angular.isDefined(scope.element.required) && scope.element.required) {
            scope.error = true;
          }
        });
        scope.$on("success", function (evt, data) {
          if (angular.isDefined(scope.element.required) && scope.element.required) {
            scope.error = false;
          }
        });
      }
    }
  }]);

  /**
   * Fieldset.
   */
  m.directive('feFieldset', ['$filter', '$compile', function ($filter, $compile) {
    return {
      scope: {
        name: '@',
        value: '=ngModel',
        element: '=',
      },
      template: '<fieldset class="node-form-options collapsible form-wrapper collapse-processed" ng-class="{collapsed: collapsed==true}" id="{{id}}">'+
      '<legend><div class="fieldset-legend"><a class="fieldset-title" ng-click="collapsibleToggle()">{{title}}</a><div class="summary">{{value.message}}</div></div></legend>'+
      '<div class="fieldset-wrapper" ng-hide="collapsed"><span>Placeholder</span></div>'+
      '</fieldset>',
      link: function (scope, elem, attr) {
        scope.collapsed = scope.element.collapsed;
        scope.collapsibleToggle = function () {
          scope.collapsed = !scope.collapsed;
        }

        scope.$on("editUrlAliasClicked", function (evt, data) {
          if (data.fieldset == scope.name) {
            scope.collapsed = !data.collapsed;
          }
        });

        scope.collapsible = scope.element.collapsible;
        scope.title = scope.element.title;
        scope.id = $filter('idClean')(scope.element.name, 'edit');
        var copy = elem.find('span').clone();
        copy.attr('fieldset-'+$filter('idClean')(scope.element.name), '');
        copy.attr('element', 'element');
        copy.attr('input-id', scope.id);
        copy.attr('ng-model', 'value');
        elem.find('span').replaceWith(copy);
        copy = $compile(copy)(scope);
      }
    }
  }]);

  m.directive('fieldsetAuthor', ['$http', '$q', function ($http, $q) {
    return {
      restrict: 'A',
      scope: {
        value: '=ngModel',
        element: '='
      },
      template: '<div class="form-item form-type-textfield postedby-name">'+
      '<label for="edit-name">{{author.title}}</label>'+
      '<input type="text" ng-model="author.value" typeahead="username for username in getUsername($viewValue)" typeahead-no-results="noResults" class="form-text form-autocomplete">'+
      '<div ng-show="noResults">No Results Found</div>'+
      '<div class="description">{{author.description}}</div>'+
      '<div class="form-item form-type-textfield form-item-date">'+
        '<label for="edit-date">{{date.title}}</label>'+
        '<input type="text" ng-model="date.value" size="60" maxlength="{{date.maxlength}}" class="form-text">'+
        '<div class="description" ng-bind-html="date.description"></div>'+
      '</div>',
      link: function (scope, elem, attr) {
        var baseUrl = Drupal.settings.basePath;
        var queryArgs = {};
        var config = {
          params: queryArgs
        };
        scope.noResults = false;
        scope.getUsername = function(username) {
          var results = [];
          if (username.length > 3) {
            return $http.get(baseUrl+'user/autocomplete/'+username, config).then(function(response) {
              for (var k in response.data) {
                results.push(response.data[k]);
              }
              scope.noResults = (results.length > 0) ? false : true;
              return results;
            });
          }
          else {
            return results;
          }
        };
        scope.author = {
          title: scope.element.author_name['#title'],
          value: (scope.element.author_name['#default_value'].length == 0) ? Drupal.settings.user_panel.user.name : scope.element.author_name['#default_value'],
          description: scope.element.author_name['#description']
        }
        scope.date = {
          title: scope.element.date['#title'],
          value: scope.element.date['#default_value'],
          description: scope.element.date['#description'],
          maxlength: scope.element.date['#maxlength']
        }
        scope.$watchGroup(['author.value', 'date.value'], function() {
          var message = (scope.author.value.length > 0) ? 'By '+scope.author.value : 'By Anonymous';
          message += (scope.date.value.length > 0) ? ' on '+scope.date.value : '';
          message = '('+message+')';
          scope.value = {
            message: message,
            fields: {
              author: scope.author.value,
              date: scope.date.value
            }
          };
        });
      }
    };

  }]);

  m.directive('fieldsetOsMenu', [function () {
    return {
      restrict: 'A',
      scope: {
        value: '=ngModel',
        element: '='
      },
      template: '<div class="form-item form-type-checkbox">'+
      '<input type="checkbox" ng-checked="osMenuEnabled.defaultValue" ng-model="osMenuEnabled.defaultValue" name="enabled" value="1" class="form-checkbox">'+
      '<label class="option" for="edit-os-menu-enabled"> {{osMenuEnabled.title}}</label>'+
      '</div>'+
      '<div class="form-wrapper" ng-show="osMenuEnabled.defaultValue">'+
        '<div class="form-item" ng-repeat="(key, field) in formElementsLink">'+
        '<div form-element element="field" value="formDataLink[key]"><span>placeholder</span></div>'+
      '</div>',
      link: function (scope, elem, attr) {
        scope.osMenuEnabled = {
          defaultValue: scope.element.enabled['#default_value'],
          title: scope.element.enabled['#title'],
        };
        scope.$on("getNodeTitle", function (evt, data) {
          if (data.value) {
            scope.formDataLink.link_title = data.value;
          }
        });
        var linkElements = scope.element.link;
        scope.formElementsLink = {};
        scope.formDataLink = {};
        for (var k in linkElements) {
          if (k == 'parent' || k == 'link_title') {
            scope.formDataLink[k] = linkElements[k]['#default_value'] || null;
            var attributes = {
              name: k
            };
            for (var j in linkElements[k]) {
              if (j.indexOf('#') === 0) {
                var attrs = j.substr(1, j.length);
                attributes[attrs] = linkElements[k][j];
              }
            }
            scope.formElementsLink[k] = attributes;
          }
        }
        scope.$watchGroup(['osMenuEnabled.defaultValue', 'formDataLink.link_title'], function() {
          var message = '';
          if (scope.formDataLink.link_title) {
            message = '(' + scope.formDataLink.link_title + ')';
          }
          if (!scope.osMenuEnabled.defaultValue) {
            message = '(Not in menu)';
            scope.formDataLink.link_title = angular.element("input[type='textfield'][name='title']").val();
          }
          scope.formDataLink.enabled = scope.osMenuEnabled.defaultValue;
          scope.value = {
            message: message,
            fields: {
              os_menu: scope.formDataLink
            }
          };
        });
      }
    };

  }]);

  m.directive('fieldsetPath', ['$rootScope', function ($rootScope) {
    return {
      restrict: 'A',
      scope: {
        value: '=ngModel',
        element: '='
      },
      template: '<div class="form-item form-type-checkbox form-item-path-pathauto">'+
      '<input type="checkbox" id="edit-path-pathauto" ng-model="pathauto.defaultValue" name="pathauto" value="1" ng-checked="pathauto.defaultValue" class="form-checkbox">'+
      '<label class="option" for="edit-path-pathauto"> {{pathauto.title}}</label>'+
      '<div class="description">{{pathauto.description}}</div></div>'+
      '<div class="form-item form-type-textfield form-item-path-alias">'+
        '<label for="edit-path-alias">{{pathalias.title}}</label>'+
        '<span class="field-prefix">{{pathalias.vsiteHome}}</span>'+
        '<input type="text" id="edit-path-alias" ng-disabled="pathauto.defaultValue" name="alias" ng-model="pathalias.defaultValue" maxlength="{{pathalias.maxlength}}" class="form-text">'+
      '</div>',
      link: function (scope, elem, attr) {
        var vsiteHome = angular.isDefined(Drupal.settings.paths.vsite_home) ? Drupal.settings.paths.vsite_home : '';
        
        scope.$on("getUrlAlias", function (evt, data) {
          scope.pathalias.defaultValue = data.alias;
        });

        scope.pathauto = {
          title: scope.element.pathauto['#title'],
          description: scope.element.pathauto['#description'],
          defaultValue: scope.element.pathauto['#default_value']
        };
        scope.pathalias = {
          title: scope.element.alias['#title'],
          defaultValue: scope.element.alias['#default_value'],
          maxlength: scope.element.alias['#maxlength'],
          vsiteHome: vsiteHome
        };
        var message = '(No alias)';
        scope.$watchGroup(['pathalias.defaultValue', 'pathauto.defaultValue'], function(newValue) {
          $rootScope.$broadcast('getPathautoStatus', scope.pathauto.defaultValue);
          if (scope.pathauto.defaultValue) {
            message = '(Automatic alias)';
          } else {
            message = (scope.pathauto.defaultValue || scope.pathalias.defaultValue.length === 0) ? '(No Alias)' : '(Alias: '+scope.pathalias.defaultValue+')';
          }
          scope.value = {
            message: message, 
            fields: {
              pathauto: scope.pathauto.defaultValue, 
              pathalias: scope.pathalias.defaultValue
            }
          };
        });
      }
    };
  }]);

  m.directive('fieldsetRevisionInformation', renderFieldsetElements);

  m.directive('fieldsetOptions', renderFieldsetElements);

  m.directive('fieldsetOsCssClassFieldset', renderFieldsetElements);

  m.directive('fieldsetOsSeo', renderFieldsetElements);
  
  m.directive('feOsWysiwygExpandingTextarea', ['$parse', '$q', '$document', function ($parse, $q, $document) {
    return {
      restrict: 'A',
      require: '?ngModel',
      scope: {
        name: '@',
        value: '=ngModel',
        element: '=',
      },
      template: '<label for="{{id}}-ckeditor">{{title}}</label>'+
        '<textarea cols="60" rows="5" class="text-full os-wysiwyg-expandable wysiwyg-angular form-textarea" ng-model="value" id="edit-body-ckeditor" name="{{name}}"></textarea>'+
        '<select class="filter-list form-select" id="edit-body-format" style="display: none;">'+
          '<option value="filtered_html" selected="selected">Filtered HTML</option>'+
          '<option value="full_html">Full HTML</option>'+
          '<option value="plain_text">Plain text</option>'+
        '</select>',
      link: function (scope, elem, attr, ngModel) {
        if (scope.element.default_value) {
          scope.value = scope.element.default_value[0].value;
        }
        scope.id = attr['inputId'];
        scope.title = scope.element.title;
         // @Todo: Format, Editor, Field can be dynamic but we don't know yet.
        Drupal.settings.osNodeFormWysiwyg.triggers = {'edit-body-ckeditor': {
            field: 'edit-body-ckeditor',
            formatfiltered_html: {editor:'ckeditor', status: 1, toggle: 0},
            resizable: 1,
            select: 'edit-body-format'
          }
        }
        Drupal.behaviors.attachWysiwygAngular.attach($document.context, Drupal.settings);
        var ck = CKEDITOR.instances['edit-body-ckeditor'];
        ck.on('instanceReady', function () {
          Drupal.wysiwyg.instances['edit-body-ckeditor'].setContent(ngModel.$viewValue);
        });
        function updateModel() {
          scope.$apply(function () {
            ngModel.$setViewValue(Drupal.wysiwyg.instances['edit-body-ckeditor'].getContent());
          }); 
        }
        ck.on('change', updateModel);
        ck.on('key', updateModel);
        ck.on('dataReady', updateModel);
        ngModel.$render = function (value) {
          Drupal.wysiwyg.instances['edit-body-ckeditor'].setContent(ngModel.$viewValue);
        };
      }
    };

  }]);

  m.directive('feMediaDraggableFile', [function () {
    return {
      restrict: 'A',
      scope: {
        name: '@',
        value: '=ngModel',
        element: '=',
      },
      template: '<div media-browser-field max-filesize="{{MaxFileSize}}" types="{{types}}" extensions="{{validExtensiond}}" upload-text="{{uploadText}}" droppable-text="{{droppableText}}" files="files" cardinality="{{cardinality}}" class="field-widget-media-draggable-file"></div>',
      link: function (scope, elem, attr) {
        if (scope.element.default_value) {
          scope.files = [];
          angular.forEach(scope.element.default_value, function(value, key) {
            scope.files.push(value);
          });
        }
        var directive_parameters = scope.element.custom_directive_parameters;
        scope.validExtensiond = scope.element.upload_validators.file_validate_extensions[0];
        scope.MaxFileSize = directive_parameters.max_filesize;
        scope.droppableText = directive_parameters.droppable_text;
        scope.cardinality = directive_parameters.cardinality;
        scope.uploadText = directive_parameters.upload_text;
        scope.types = directive_parameters.types;
      }
    };

  }]);

  m.directive('feOgVocabComplex', [function () {
    return {
      restrict: 'A',
      scope: {
        name: '@',
        value: '=ngModel',
        element: '=',
      },
      template: '<div class="term-applied"><div class="term-applied-header">Taxonomy</div><span>Terms applied: {{selectedTermNames}}</span></div>'+
      '<fieldset class="form-wrapper">'+
        '<div class="fieldset-wrapper">'+
          '<div class="form-item">'+
            '<taxonomy-widget entity-type="node" terms="terms" bundle="{{bundle}}" expand-option="true"></taxonomy-widget>'+
          '</div>'+
        '</div>'+
      '</fieldset>',
      link: function (scope, elem, attr) {
        scope.bundle = scope.element.bundle;
        scope.terms = scope.value || [];
        if (scope.element.default_value) {
          angular.forEach(scope.element.default_value, function(value, key) {
            scope.terms.push({id:value['target_id']});
          });
        }
        scope.$watch('terms', function(newTerms, oldTerms) {
          var selectedTermNames = '';
          var selectedTermIds = [];
          for (var k in newTerms) {
            selectedTermNames +=  (k == (newTerms.length) - 1) ? newTerms[k].label :  newTerms[k].label + ',';
            selectedTermIds.push(newTerms[k].id);
          }
          scope.selectedTermNames = selectedTermNames;
          scope.value = selectedTermIds;
        });
      }
    };
  }]);

})();
