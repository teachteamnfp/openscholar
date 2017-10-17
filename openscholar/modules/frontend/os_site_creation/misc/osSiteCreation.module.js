(function () {

  var m = angular.module('SiteCreationForm', ['angularModalService', 'ngMessages', 'os-buttonSpinner'])
  .config(function (){
    rootPath = Drupal.settings.paths.siteCreationModuleRoot;
    paths = Drupal.settings.paths
  });

  m.controller('siteCreationCtrl', ['$scope', '$http', '$q', '$rootScope', 'buttonSpinnerStatus', '$filter', function($scope, $http, $q, $rootScope, bss, $filter) {

  //Set default value for vsite
  $scope.vsite_private = {
    value: '0'
  };

  // Set site creation status
  $scope.siteCreated = false;

  //Toggle open/close for 'who can view your site'
  $scope.showAll = false;
  $scope.toggleFunc = function() {
    $scope.showAll = !$scope.showAll;
  };

  //Reset value for other 'Type of site' based on selection
  $scope.clearRest = function(field) {
    if (field != 'individualScholar') {
      $scope.individualScholar = null;
    }
    if (field != 'projectLabSmallGroup') {
      $scope.projectLabSmallGroup = null;
    }
    if (field != 'departmentSchool') {
      $scope.departmentSchool = null;
    }
  }

  //Set status of next button to disabled initially
  $scope.btnDisable = true;

  //Navigate between screens
  $scope.page1 = true;
  $scope.navigatePage = function(pagefrom, pageto) {
    $scope[pagefrom] = false;
    $scope[pageto] = true;
    if (pagefrom == 'page1' && pageto == 'page2') {
      if ($scope.individualScholar != null) {
        $scope.contentOption = {
          value: 'os_scholar',
        };
      } else if ($scope.projectLabSmallGroup != null) {
         $scope.contentOption = {
          value: 'os_project',
        };
      } else {
        $scope.contentOption = {
          value: 'os_department_minimal',
        };
      }
    } else if (pagefrom == 'page2' && pageto == 'page3') {
      var featuredThemeTop = angular.element(document.querySelectorAll('.featured-scrolltop')).position().top;
      featuredThemeTop = featuredThemeTop > 0 ? featuredThemeTop : 650;
      angular.element(document.querySelectorAll('#body-container-page3')).animate({ scrollTop: featuredThemeTop + 200}, 200);
    }
  }

  var queryArgs = {};
  var promises = [];
    if (Drupal.settings.spaces != undefined) {
      if (Drupal.settings.spaces.id) {
        queryArgs.vsite = Drupal.settings.spaces.id;
      }
    }
    var config = {
      params: queryArgs
    };
    $http.get(paths.api+'/themes', config).then(function (response) {
      $scope.themes = response.data.data;
    });
    $scope.selected = false;
    $scope.selectedOption = {key: 'default'};
    $scope.setTheme = function(themeKey, flavorKey) {
      $scope.selected = themeKey + '-os_featured_flavor-' + flavorKey;
    }

    $scope.changeSubTheme = function(item, themeKey) {      
      angular.forEach($scope.themes.others, function(value, key) {
        if (value.themeKey == themeKey) {
          $scope.themes.others[key].flavorKey = item.key;
          angular.forEach(value.flavorOptions, function(v, k) {
            if (v.key == item.key) {
              if (v.screenshot != '/') {
                $scope.themes.others[key].screenshot = v.screenshot;
              } else {
                $scope.themes.others[key].screenshot = $scope.themes.others[key].defaultscreenshot;
              }
            }
          });
        }
      });
    }

    $scope.navigateToSite = function(themeKey) {
      window.location.href = $scope.vsiteUrl;
    }

  //Set default value for Content Option
  $scope.contentOption = {
    value: 'os_department_minimal',
  };
  $scope.selected = 'hwpi_classic-os_featured_flavor-default';
  //Site URL
  $scope.baseURL = Drupal.settings.admin_panel.purl_base_domain + '/';

  //Get all values and save them in localstorage for use
  $scope.saveAllValues = function() {
    bss.SetState('site_creation_form', true);
    $scope.btnDisable = true;
    var formdata = {};
    formdata = {
      individualScholar: $scope.individualScholar,
      projectLabSmallGroup: $scope.projectLabSmallGroup,
      departmentSchool: $scope.departmentSchool,
      vsite_private: $scope.vsite_private.value,
      contentOption: $scope.contentOption.value,
     };

    // Get sub site parent id
    if (typeof $rootScope.siteCreationFormId !== 'undefined') {
      var splitId = $rootScope.siteCreationFormId.split('add-subsite-');
      if (splitId.length > 1) {
        formdata['parent'] = splitId[1];
      }
    }

    // Send the theme key
    if (typeof $scope.selected !== 'undefined') {
      formdata['themeKey'] = $scope.selected;
    }
    console.log(formdata);
    $http.post(paths.api + '/purl', formdata).then(function (response) {
      console.log(response);
      $scope.successData = response.data.data.data;
      $scope.vsiteUrl = response.data.data.data;
      $scope.siteCreated = true;
    });
  }
}]);
  /**
   * Open modals for the site creation forms
   */
  m.directive('siteCreationForm', ['ModalService','$rootScope', function (ModalService,$rootScope) {
    var dialogOptions = {
      minWidth: 900,
      minHeight: 300,
      modal: true,
      position: 'center',
      dialogClass: 'site-creation-form'
    };

    function link(scope, elem, attrs) {
      elem.bind('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $rootScope.siteCreationFormId = attrs.id;

        ModalService.showModal({
          controller: 'siteCreationCtrl',
          templateUrl: rootPath + '/templates/os_site_creation.html',
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

//Validate form for existing site names
  m.directive('formcheckDirective', ['$http', function($http) {
  var responseData;
  return {
    require: 'ngModel',
    link: function(scope, element, attr, siteCreationCtrl) {
      function formValidation(ngModelValue) {
        siteCreationCtrl.$setValidity('isinvalid', true);
        siteCreationCtrl.$setValidity('sitename', true);
        siteCreationCtrl.$setValidity('permission', true);
        scope.btnDisable = true;
        var baseUrl = Drupal.settings.paths.api;
        if(ngModelValue){
          //Ajax call to get all existing sites
          $http.get(baseUrl + '/purl/' + ngModelValue).then(function mySuccess(response) {
            responseData = response.data.data;
            if (responseData.msg == "Not-Permissible") {
              siteCreationCtrl.$setValidity('permission', false);
              siteCreationCtrl.$setValidity('sitename', true);
              siteCreationCtrl.$setValidity('isinvalid', true);
              scope.btnDisable = true;
            }
            else if (responseData.msg == "Invalid"){
              siteCreationCtrl.$setValidity('permission', true);
              siteCreationCtrl.$setValidity('sitename', true);
              siteCreationCtrl.$setValidity('isinvalid', false);
              scope.btnDisable = true;
            }
            else if (responseData.msg == "Not-Available") {
              siteCreationCtrl.$setValidity('permission', true);
              siteCreationCtrl.$setValidity('isinvalid', true);
              siteCreationCtrl.$setValidity('sitename', false);
              scope.btnDisable = true;
            }
            else{
              siteCreationCtrl.$setValidity('permission', true);
              siteCreationCtrl.$setValidity('isinvalid', true);
              siteCreationCtrl.$setValidity('sitename', true);
              scope.btnDisable = false;
            }
          });
        }
        return ngModelValue;
      }
      siteCreationCtrl.$parsers.push(formValidation);
    }
  };
}]);
jQuery(document).ready(function(){
 var highestBox = 0;
        jQuery('.starter-content .form-item').each(function(){  
                if(jQuery(this).height() > highestBox){  
                highestBox = jQuery(this).height();  
        }
    });    
    jQuery('.starter-content .form-item').height(highestBox);

});

})()


