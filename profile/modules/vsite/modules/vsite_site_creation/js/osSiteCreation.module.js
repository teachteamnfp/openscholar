(function () {

  let m = angular.module('SiteCreationForm', ['angularModalService', 'ngMessages', 'os-buttonSpinner', 'os-auth', 'ActiveUser', 'DependencyManager', 'ngSanitize', 'DrupalSettings', 'UrlGenerator']);

  // m.run(['Dependencies', function (dm) {
  //   let deps = dm.GetDependencies('UserPanel');
  //   Array.prototype.push.apply(m.requires, deps);
  // }]);

  m.service('passwordStrength', [function () {
    let tests = [/[0-9]/, /[a-z]/, /[A-Z]/, /[^A-Z-0-9]/i];
    this.checkStrength = function(pass) {
      if (pass == null) {
        return -1;
      }
      let s = 0;
      if (pass.length < 6) {
        return 0;
      }
      for (let i in tests) {
        if (tests[i].test(pass)) {
          s++;
        }
      }
      return s;
    }
  }]);

  m.controller('siteCreationCtrl', ['$scope', '$http', '$q', '$rootScope', 'buttonSpinnerStatus', '$filter', '$sce', '$timeout', 'passwordStrength', 'authenticate-token', 'ActiveUserService', 'drupalSettings', 'urlGenerator', 'parent', function($scope, $http, $q, $rootScope, bss, $filter, $sce, $timeout, ps, at, aus, settings, url, parent) {

  //Set default value for vsite
  $scope.vsite_private = {
    value: 'public'
  };
  $scope.privacyLevels = settings.fetchSetting('site_creation.privacy_levels');
  $scope.trustAsHtml = function (arg) {
    return $sce.trustAsHtml(arg);
  };

  let user;
  aus.getUser(function (u) {
    user = u;

    let types = {
        'create personal group': true,
        'create project group': true,
        'create department group': true
      };
    for (let i in user.permissions) {
      let perm = user.permissions[i];
      if (types[perm] != undefined) {
        switch (perm) {
          case 'create personal group':
            $scope.display = 'individualScholar';
            break;
          case 'create project group':
            $scope.display = 'projectLabSmallGroup';
            break;
          case 'create department group':
            $scope.display = 'department';
            break;
        }
      }
    }
  });

  // Set site creation status
  $scope.siteCreated = false;
  $scope.tos = settings.fetchSetting('site_creation.tos_url');
  $scope.tos_label = settings.fetchSetting('site_creation.tos_label');

  let presetList = settings.fetchSetting('site_creation.presets');
  $scope.presets = function (type) {
    let output = [];
    for (let i in presetList) {
      if (presetList[i].site_type == type) {
        output.push(presetList[i]);
      }
    }
    return output;
  };

  // Initialize the $timout let
  let timer;

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
  };

  //Set status of next button to disabled initially
  $scope.btnDisable = true;
  $scope.canCreateUser = settings.fetchSetting('site_creation.hasOsId');
  $scope.showUserCreation = !settings.fetchSetting('user.uid');
  $scope.userCreationOptional = $scope.canCreateUser && !$scope.showUserCreation;
  $scope.siteNameValid = false;
  $scope.newUserResistrationEmail = false;
  $scope.newUserResistrationName = false;
  $scope.newUserResistrationPwd = false;
  $scope.newUserValidPwd = false;
  $scope.newUserResistrationPwdMatch = false;
  $scope.tosChecked = !$scope.tos;  // circumvent if no tos was provided by site

  //Navigate between screens
  $scope.page1 = true;
  $scope.navigatePage = function(pagefrom, pageto) {
    $scope[pagefrom] = false;
    $scope[pageto] = true;
    if (pagefrom == 'page1' && pageto == 'page2') {
      if ($scope.individualScholar != null) {
        $scope.contentOption = {
          value: 'os_scholar'
        };
      } else if ($scope.projectLabSmallGroup != null) {
         $scope.contentOption = {
          value: 'os_project'
        };
      } else {
        $scope.contentOption = {
          value: 'os_department_minimal'
        };
      }
    } else if (pagefrom == 'page2' && pageto == 'page3') {
      let featuredThemeTop = angular.element(document.querySelectorAll('.featured-scrolltop')).position().top;
      featuredThemeTop = featuredThemeTop > 0 ? featuredThemeTop : 650;
      angular.element(document.querySelectorAll('#body-container-page3')).animate({ scrollTop: featuredThemeTop + 200}, 200);
    }
  };

  $scope.canBeCreated = function (type) {
    if (user && user.uid != 1 && user.permissions.indexOf('create ' +type+' group') == -1) {
      return false;
    }

    if (!parent) {
      return true;
    }

    return settings.hasSetting('site_creation.subsite_types.'+type);
  };

  let queryArgs = {};
  if (settings.hasSetting('spaces.id')) {
    queryArgs.vsite = settings.fetchSetting('spaces.id');
  }

  $scope.themes = settings.fetchSetting('site_creation.themes');
  $scope.selected = false;
  $scope.selectedOption = {key: 'default'};
  $scope.setTheme = function(themeKey) {
    $scope.selected = themeKey;
  };

  $scope.changeSubTheme = function(item, themeKey) {
    angular.forEach($scope.themes.others, function(value, key) {
      if (value.themeKey == themeKey) {
        $scope.themes.others[key].flavorKey = item.key;
        angular.forEach(value.flavorOptions, function(v, k) {
          if (v.key == item.key) {
            if (v.screenshot.indexOf('png') > -1) {
              $scope.themes.others[key].screenshot = v.screenshot;
            } else {
              $scope.themes.others[key].screenshot = $scope.themes.others[key].defaultscreenshot;
            }
          }
        });
      }
    });
  };

  $scope.navigateToSite = function() {
    window.location.href = $scope.vsiteUrl;
  };

  //Set default value for Content Option
  $scope.contentOption = {
    value: 'os_department_minimal'
  };
  $scope.selected = 'hwpi_classic';
  //Site URL
  $scope.baseURL = settings.fetchSetting('path.baseUrl');

  //Get all values and save them in localstorage for use
  $scope.saveAllValues = function() {
    bss.SetState('site_creation_form', true);
    $timeout.cancel(timer);

    $scope.btnDisable = true;
    let url = $scope.individualScholar ? $scope.individualScholar : ($scope.projectLabSmallGroup ? $scope.projectLabSmallGroup : $scope.departmentSchool),
      bundle = $scope.individualScholar ? 'personal' : ($scope.projectLabSmallGroup ? 'project' : 'department'),
      theme = $scope.selected;

    let userData = {};
    if ($scope.showUserCreation) {
      // brand new user
      userData.field_first_name = $scope.fname;
      userData.field_last_name = $scope.lname;
      userData.mail = $scope.email;
      userData.name = $scope.userName;
      userData.pass = $scope.confirmPwd;

      $scope.submitStateText = 'User Information...';
      $http.post(settings.fetchSetting('paths.api')+'/user?_format=json', userData).then(function (response) {
        submitGroup(response.data.uid, url, bundle, $scope.contentOption.value, theme, $scope.vsite_private.value);
      });
    }
    else if (user != undefined && user.uid) {
      // existing user who came to the page logged in
      userData.uid = user.uid;
    }

    if (userData.uid) {
      submitGroup(user.uid, url, bundle, $scope.contentOption.value, theme, $scope.vsite_private.value)
    }
  };

  function submitGroup(owner, purl, bundle, starter, theme, privacy) {
    let fields = {
      owner: owner,
      label: purl,
      type: bundle,
      purl: purl,
      preset: starter,
      theme: theme,
      privacy: privacy
    };
    if (parent) {
      fields.parent = parent;
    }
    $http.post(url.generate(settings.fetchSetting('paths.api'))+'/group?_format=json', fields).then(function (response) {
      if (response.data.id != undefined) {
        bss.SetState('site_creation_form', false);
        $scope.submitSuccess = true;
        $scope.submitting = false;
        $scope.submitted = true;
        $scope.siteCreated = true;
        let headers = response.headers();
        if (headers['x-drupal-batch-id']) {
          $scope.vsiteUrl = headers['location'] + '/batch?id=' + headers['x-drupal-batch-id'] + '&op=start';
        } else {
          $scope.vsiteUrl = headers['location'];
        }
      }
      // gotta figure out what an error looks like
    }, function (errorResponse) {
      bss.SetState('site_creation_form', false);
      $scope.submitError = errorResponse.data.title;
      $scope.submtitted = true;
      $scope.submitting = false;
    });
  }

  $scope.checkUserName = function() {
    $scope.newUserResistrationName = false;
    if (typeof $scope.userName !== 'undefined' && $scope.userName != '') {
      let formdata = {
        name: $scope.userName
      };
      $http.post(url.generate(settings.fetchSetting('paths.api')) + '/user/validate?_format=json', formdata).then(function (response) {
        if (response.data.length == 0) {
          $scope.showUserError = false;
          $scope.userErrorMsg = '';
          $scope.newUserResistrationName = true;
        } else {
          $scope.showUserError = true;
          $scope.userErrorMsg = $sce.trustAsHtml(response.data[0]);
        }
      });
    }
    $scope.isCompletedRes();
  };

  $scope.checkEmail = function() {
    $scope.newUserResistrationEmail = false;
    if (typeof $scope.email !== 'undefined' && $scope.email != '') {
      let formdata = {
        email: $scope.email
      };
      $http.post(url.generate(settings.fetchSetting('paths.api')) + '/user/validate?_format=json', formdata).then(function (response) {
        if (response.data.length == 0) {
          $scope.showEmailError = false;
          $scope.emailErrorMsg = '';
          $scope.newUserResistrationEmail = true;
        } else {
          $scope.showEmailError = true;
          $scope.emailErrorMsg = $sce.trustAsHtml(response.data[0]);
        }
      });
    }
    $scope.isCompletedRes();
  };

  $scope.checkPwd = function() {
    $scope.newUserResistrationPwd = false;
    if (typeof $scope.password !== 'undefined' && $scope.password != '') {
      let formdata = {
        password: $scope.password
      };
      $http.post(url.generate(settings.fetchSetting('paths.api')) + '/user/validate?_format=json', formdata).then(function (response) {
        if (response.data.length == 0) {
          $scope.showPwdError = false;
          $scope.pwdErrorMsg = '';
          $scope.newUserResistrationPwd = true;
        } else {
          $scope.showPwdError = true;
          $scope.pwdErrorMsg = $sce.trustAsHtml(response.data[0]);
        }
      });
    }
    $scope.isCompletedRes();
  };

  let userCreationValid = false;
  $scope.isCompletedRes = function() {
    timer = $timeout(function () {
      if ($scope.newUserResistrationEmail && $scope.newUserResistrationName && $scope.newUserValidPwd && $scope.newUserResistrationPwd && $scope.newUserResistrationPwdMatch) {
        userCreationValid = true;
      } else {
        userCreationValid = false;
      }
    }, 2000);
  };

  $scope.validateForms = function() {
      if ($scope.btnDisable) {
        return true;
      }
      if ($scope.showUserCreation && userCreationValid) {
        return !$scope.tosChecked;
      }
      if ($scope.showUserCreation && !userCreationValid) {
        return true;
      }
      if (!$scope.showUserCreation) {
        return !$scope.tosChecked;
      }
  };

  $scope.score = function() {
    $scope.newUserValidPwd = false;
    let pwdScore = ps.checkStrength($scope.password);
    if (pwdScore < 1 ) {
      $scope.strength = "At least 6 characters";
    } else if (pwdScore == 1) {
      $scope.strength = "Weak";
    } else if (pwdScore == 2) {
      $scope.strength = "Good";
    } else if (pwdScore == 3) {
      $scope.strength = "Fair";
    } else if (pwdScore > 3) {
      $scope.strength = "Strong";
    }
    if (pwdScore > 0) {
      $scope.newUserValidPwd = true;
    }
    return pwdScore;
  };

 $scope.pwdMatch = function() {
  $scope.newUserResistrationPwdMatch = false;
  if (typeof $scope.password !== 'undefined' && $scope.password != '') {
    if (angular.equals($scope.password, $scope.confirmPwd)) {
      $scope.newUserResistrationPwdMatch = true;
      $scope.isCompletedRes();
      return 'yes';
    } else {
      return 'no';
    }
  } else {
    return '';
  }
 }
}]);
  /**
   * Open modals for the site creation forms
   */
  m.directive('siteCreationForm', ['ModalService', '$rootScope', 'drupalSettings', 'urlGenerator', function (ModalService, $rootScope, settings, url) {
    let dialogOptions = {
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

        openModal(scope);
      });

      if (scope.autoOpen) {
        openModal(scope);
      }

      function openModal(scope) {
        ModalService.showModal({
          controller: 'siteCreationCtrl',
          templateUrl: url.generate(settings.fetchSetting('paths.site_creation')) + '/os_site_creation.html',
          inputs: {
            form: scope.form,
            parent: scope.parent
          }
        })
        .then(function (modal) {
          dialogOptions.title = scope.title || '';
          dialogOptions.close = function (event, ui) {
            modal.element.remove();
          };
          modal.element.dialog(dialogOptions);
          modal.close.then(function (result) {
            if (result) {
              window.location.reload();
            }
          });
        });
      }
    }

    return {
      link: link,
      scope: {
        form: '@',
        autoOpen: '@?',
        parent: '@'
      }
    };
  }]);

//Validate form for existing site names
  m.directive('formcheckDirective', ['$http', 'drupalSettings', 'urlGenerator', function($http, settings, url) {
  let responseData;
  return {
    require: 'ngModel',
    link: function(scope, element, attr, siteCreationCtrl) {

      function formValidation(ngModelValue) {
        siteCreationCtrl.$setValidity('isinvalid', true);
        siteCreationCtrl.$setValidity('sitename', true);
        siteCreationCtrl.$setValidity('permission', true);
        scope.btnDisable = true;
        let baseUrl = url.generate(settings.fetchSetting('paths.api'));
        if(ngModelValue){
          //Ajax call to get all existing sites
          $http.get(baseUrl + '/group/validate/url/' + encodeURIComponent(ngModelValue)+'?_format=json').then(function mySuccess(response) {
            responseData = response.data;
            if (responseData.msg == "Not-Permissible") {
              siteCreationCtrl.$setValidity('permission', false);
              siteCreationCtrl.$setValidity('isinvalid', true);
              siteCreationCtrl.$setValidity('sitename', true);
              scope.btnDisable = true;
              scope.siteNameValid = false;
            }
            else if (responseData.msg == "Invalid"){
              siteCreationCtrl.$setValidity('permission', true);
              siteCreationCtrl.$setValidity('isinvalid', false);
              siteCreationCtrl.$setValidity('sitename', true);
              scope.btnDisable = true;
              scope.siteNameValid = false;
            }
            else if (responseData.msg == "Not-Available") {
              siteCreationCtrl.$setValidity('permission', true);
              siteCreationCtrl.$setValidity('isinvalid', true);
              siteCreationCtrl.$setValidity('sitename', false);
              scope.btnDisable = true;
              scope.siteNameValid = false;
            }
            else{
              siteCreationCtrl.$setValidity('permission', true);
              siteCreationCtrl.$setValidity('isinvalid', true);
              siteCreationCtrl.$setValidity('sitename', true);
              scope.siteNameValid = true;
              if (scope.vicariousUser) {
                scope.isCompletedRes();
              } else {
                scope.btnDisable = false;
              }
            }
          }, function (response) {
            // this triggers if the entered URL has a slash or backslash in it
            if (response.status == 404 || response.status == -1) {
              siteCreationCtrl.$setValidity('permission', true);
              siteCreationCtrl.$setValidity('isinvalid', false);
              siteCreationCtrl.$setValidity('sitename', true);
              scope.btnDisable = true;
              scope.siteNameValid = false;
            }
            else {
              // error on server end
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
  let highestBox = 0;
  jQuery('.starter-content .form-item').each(function(){
    if (jQuery(this).height() > highestBox) {
      highestBox = jQuery(this).height();
    }
  });
  jQuery('.starter-content .form-item').height(highestBox);

});

})();


