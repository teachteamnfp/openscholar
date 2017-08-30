(function () {

  var m = angular.module('basicFormElements', ['osHelpers', 'ngSanitize', 'EntityService']);

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
      template: '<input type="checkbox" id="{{id}}" name="{{name}}" value="1" class="form-checkbox" ng-model="value" ng-disabled="element.disabled" ng-true-value="1" ng-false-value="0"/><label class="option" for="{{id}}">{{title}}</label>',
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
   * Fieldset.
   */
  m.directive('feFieldset', ['$filter', function ($filter) {
    return {
      scope: {
        name: '@',
        value: '=ngModel',
        element: '=',
      },
      template: '<fieldset class="node-form-options collapsible form-wrapper collapse-processed" ng-class="{collapsed: collapsed==true}" id="{{id}}">'+
        '<legend><span class="fieldset-legend"><span class="fieldset-title" ng-click="collapsibleToggle()">{{title}}</span>'+
        '<span class="summary"> (Selected value will go here)</span></span></legend>'+
        '<div class="fieldset-wrapper-element" ng-hide="collapsed"><div class="form-item" ng-repeat="(key, field) in formElements">'+
        '<div form-element element="field" value="formData[key]"><span>placeholder</span></div></div></div></fieldset>',
      link: function (scope, elem, attr) {
        scope.collapsed = scope.element.collapsed;
        scope.collapsibleToggle = function () {
          scope.collapsed = !scope.collapsed;
        }
        scope.collapsible = scope.element.collapsible;
        scope.formElements = {};
        scope.formData = {};
        scope.title = scope.element.title;
        scope.id = $filter('idClean')(scope.element.name, 'edit');
        var formElementsRaw = scope.element;
        for (var formElem in formElementsRaw) {
          // @todo: we need to write a directive for container.
          if (angular.isObject(formElementsRaw[formElem]) && angular.isDefined(formElementsRaw[formElem]['#type']) && formElementsRaw[formElem]['#type'] != 'container' ) {
            scope.formData[formElem] = formElementsRaw[formElem]['#default_value'] || null;
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
            scope.formElements[formElem] = attributes;
          }
        }
      }
    }
  }]);

  m.directive('feValue', [function () {
    return {
      scope: {
        name: '@',
        value: '=ngModel',
        element: '='
      },
      template: '<input type="hidden" id="{{id}}" name="{{name}}" ng-model="value" class="form-text" ng-disabled="element.disabled">',
      link: function (scope, elem, attr) {
        scope.id = attr['inputId'];
      }
    };
  }])

  m.directive('feOsWysiwygExpandingTextarea', ['$parse', '$q', '$document', function ($parse, $q, $document) {
    return {
      restrict: 'A',
      scope: {
        name: '@',
        value: '=ngModel',
        element: '=',
      },
      template: '<label for="{{id}}-ckeditor">{{title}}</label>'+
        '<textarea cols="60" rows="5" class="text-full os-wysiwyg-expandable wysiwyg-angular form-textarea" id="edit-body-ckeditor" name="{{name}}"></textarea>'+
        '<select class="filter-list form-select" id="edit-body-format" style="display: none;">'+
        '<option value="filtered_html" selected="selected">Filtered HTML</option><option value="full_html">Full HTML</option><option value="plain_text">Plain text</option>'+
        '</select>',
      link: function (scope, elem, attr) {
        scope.id = attr['inputId'];
        scope.title = scope.element.title;
        // @todo Format, Editor, Field can be dynamic but we don't know yet.
        Drupal.settings.osNodeFormWysiwyg.triggers = {'edit-body-ckeditor': {
            field: 'edit-body-ckeditor',
            formatfiltered_html: {editor:'ckeditor', status: 1, toggle: 0},
            resizable: 1,
            select: 'edit-body-format'
          }
        }
        Drupal.behaviors.attachWysiwygAngular.attach($document.context, Drupal.settings);
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
      template: '<div class="field-widget-media-draggable-file" media-browser-field max-filesize="128 MB"'+
        'types="audio,default,document,executable,html,image,video"'+ 
        'extensions="jpg jpeg gif png txt doc docx gz xls xlsx pdf ppt pptx pps odt ods odp tex tgz xml zip sas"'+
        'upload-text="Upload" droppable-text="Drop files here to upload" files="files" cardinality="-1">',
      link: function (scope, elem, attr) {
        console.log(scope.element);
      }
    };

  }]);

  m.directive('feOgVocabComplex', ['EntityService', function (EntityService) {

    return {
      restrict: 'A',
      scope: {
        name: '@',
        value: '=ngModel',
        element: '=',
      },
      template: '<taxonomy-widget entity-type="node" terms="terms" bundle="page"></taxonomy-widget>',
      link: function (scope, elem, attr) {
        console.log(scope.element);
        scope.terms = [];
      }
    };

  }]);

})();
