(function () {

  var m = angular.module('CpAppearanceCustomTheme', ['angularModalService', 'formElement', 'os-buttonSpinner']);

    /**
   * Fetches the content settings forms from the server and makes them available to directives and controllers
   */
  m.service('customTheme', ['$http', '$q', function ($http, $q) {

    var baseUrl = Drupal.settings.paths.api;
    var uploadUrl = baseUrl+'/themes';
    var http_config = {
      params: {}
    };
    if (typeof Drupal.settings.spaces != 'undefined' && Drupal.settings.spaces.id) {
      http_config.params.vsite = Drupal.settings.spaces.id;
    }
    this.uploadZipTheme = function(file,name){
      var config = [
        {orgFileName:name},
      ];
      var deffered = $q.defer();
      $http.post(uploadUrl, file, config, {
        transformRequest: angular.identity,
        headers: {'Content-Type': undefined}
      })
      .success(function (response) {
        deffered.resolve(response);
      })
      .error(function (response) {
        deffered.reject(response);
      });
      return deffered.promise;
    }

    this.fetchBranches = function(gitBranchName, flavor){
      var vals = {
        git: gitBranchName,
        flavor: flavor,
      };
      return $http.post(uploadUrl, vals, http_config).then(function (r) {
          return(r.data);
      });
    }

    this.submitGit = function(repo, branch, filepath) {
      var vals = {
        repo: repo,
        branch: branch,
        path: filepath,
      };
      return $http.put(uploadUrl, vals, http_config).then(function (r) {
        return(r.data);
      });
    }

    this.editGit = function(branch, flavor) {
      var vals = {
        branch: branch,
        flavor: flavor,
      };
      editUrl = uploadUrl + '/git';
      return $http.put(editUrl, vals, http_config).then(function (r) {
        return(r.data);
      });
    }

    this.getFlavorName = function(flavor) {
      getUrl = uploadUrl + '/' + flavor;
      return $http.get(getUrl, http_config).then(function (r) {
        return(r.data);
      });
    }

    this.deleteTheme = function(flavor) {
      deleteUrl = uploadUrl + '/' + flavor;
      return $http.delete(deleteUrl, http_config).then(function (r) {
        return(r.data);
      });
    }

  }]);

  /**
   * To handle file upload
   */
  m.directive('fileModel', ['$parse', function ($parse) {
    return {
       restrict: 'A',
       link: function(scope, element, attrs) {
          var model = $parse(attrs.fileModel);
          var modelSetter = model.assign;
          element.bind('change', function(){
             scope.$apply(function(){
                modelSetter(scope, element[0].files[0]);
             });
          });
       }
    };
  }]);


  /**
   * Open modals for the custom theme upload
   */
  m.directive('cpAppearanceCustomTheme', ['ModalService', 'customTheme', function (ModalService, customTheme) {
    var dialogOptions = {
      minWidth: 1150,
      minHeight: 100,
      modal: true,
      position: 'center',
      dialogClass: 'ap-settings-form'
    };

    function link(scope, elem, attrs) {
      //apSettings.SettingsReady().then(function () {
        scope.title = 'Themes';
      //});

      elem.bind('click', function (e) {
        e.preventDefault();
        e.stopPropagation();

        ModalService.showModal({
          controller: 'customThemeController',
          template: 
            '<div id="custom-theme-content">'+

            '<div class="messages" ng-show="errors.length > 0"><div class="dismiss" ng-click="status.length = 0; errors.length = 0;">X</div>' +
            '<div class="error" ng-show="errors.length > 0"><div ng-repeat="m in errors track by $index"><span ng-bind-html="m"></span></div></div></div>' +

             '<div class="theme-screen" ng-show = "themeScreen"><span class="custom-theme-header"><b>Download the <a target="_blank" href="https://github.com/openscholar/starterkit">Subtheme Starter Kit</a> to begin developing your customtheme.</b></br> Use of the custom theme feature is at your own risk. The OpenScholar team is not responsible for maintaining, fixing or updating custom themes uploaded to the system. We will make every attempt possible to publish changes made to the markup used throughout OpenScholar from one code release to the next.</span>' +
             '<ul class="custom-theme-admin-list"><li class="clearfix"><span class="label">'+
             '<a href="" ng-click="ShowZip()">Zip</a></span><div class="description">Upload zip files.</div></li><li class="clearfix">'+
             '<span class="label"><a href="" ng-click="ShowGit()">Git</a></span><div class="description">'+
             'Clone from a repository.</div></li></ul></div>'+

              '<div class="zip-screen" ng-show = "zipScreen">'+
             '<form enctype="multipart/form-data" name="settingsForm" method="post" id="cp-appearance-manage-base" accept-charset="UTF-8" ng-submit="submitZipForm($event)">'+
             '<label>Themes <span class="form-required" title="This field is required.">*</span></label>'+
             '<div id="edit-file-upload-wrapper" class="form-managed-file"><input type="file" id="edit-file-upload" size="22" class="form-file" file-model="zipThemeUpload"><input type="submit" id="edit-file-upload-button" name="file_upload_button" value="Upload" ng-click = "uploadFile()"></div>'+
             '<div class="description">The uploaded image will be displayed on this page using the image style choosen below.</div>'+
             '<div class="custom-theme-help-link"><a href="https://docs.openscholar.harvard.edu/subsite-themes"  target="_blank">Learn more about Subsite Themes</a></div>' +
             '<div class="actions"><button type="submit" button-spinner="settings_form" spinning-text="Saving">Save</button><input type="button" value="Close" ng-click="close(false)"></div></form></div>'+

             '<div class="git-screen" ng-show = "gitScreen"><label for="edit-repository">Git repository address <span class="form-required" title="This field is required.">*</span></label>'+
             '<input type="text" name="repository" ng-model="gitRepo" value="" size="60" maxlength="128" ng-model="gitAddress">'+
             '<div id="branches-wrapper"><div class="form-actions form-wrapper" id="edit-actions"><a href="#" ng-click="fetchBranches()">Fetch branches</a></div></div>'+
             '<div class="custom-theme-help-link"><a href="https://docs.openscholar.harvard.edu/subsite-themes"  target="_blank">Learn more about Subsite Themes</a></div>'+
             '<div class="form-item form-type-select form-item-branch" ng-show="showBranchesSelect">' +
             '<label for="edit-branch">Branch <span class="form-required" title="This field is required.">*</span></label>' +
             '<select name="branch" ng-model="showBranches" class="form-select required" ng-options="key as value for (key, value) in BranchList"><option value="" selected="selected">- Select -</option></select>' +
             '<div class="description">Enter the branch of the git repository</div></div>' +
             '<div class="actions"><button type="submit" button-spinner="settings_form" spinning-text="Saving" ng-click="addGit()">Save</button><input type="button" value="Close" ng-click="close(false)"></div></div>'+

             '<div ng-show="gitEditScreen"><div class="form-item form-type-item"><h1>Update {{theme_name}}</h1></div>'+
             '<div id="edit-info" class="form-item form-type-item"><label for="edit-info">Git repository address </label>{{repository_address}}</div>'+
             '<div class="form-item form-type-select form-item-branch"><label for="edit-branch">Branch </label>'+
               '<select name="branch" ng-model="showEditBranches" class="form-select" ng-options="key as value for (key, value) in EditBranchList"></select></div>'+
             '<div class="description">Change the new branch or select the old one and update.</div>'+
             '<div class="actions"><button type="submit" button-spinner="settings_form" spinning-text="Updating" ng-click="editGit()">Update</button><input type="button" value="Close" ng-click="close(false)"><div id="edit-description" class="form-item form-type-item">This action will pull the latest version of the theme code from GitHub into OpenScholar.</div></div></div>'+

             '<div ng-show="deleteScreen">'+
             '<div class="description">{{flavor_name}} Deleting the sub theme will remove her files and cannot be undone.</div>'+
             '<div class="actions"><button type="submit" button-spinner="settings_form" spinning-text="Deleting" ng-click="deleteSubtheme()">Delete</button><input type="button" value="Close" ng-click="close(false)"></div></div>'+

            '</div>',
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
  
  /**
   * The controller for the forms themselves
   */
  m.controller('customThemeController', ['$scope', '$sce', 'customTheme', 'form', function ($s, $sce, ct, form) {

      $s.gitScreen = false;
      $s.zipScreen = false;
      $s.themeScreen = false;
      $s.showBranchesSelect = false;
      $s.gitEditScreen = false;
      $s.deleteScreen = false;
      $s.file = {};
      $s.path = '';
      $s.flavor = '';
      $s.errors = [];
      var formId = form;

      if(formId.indexOf("edit-subtheme") > -1) {
        $s.gitEditScreen = true;
        var flavorName = formId.split("edit-subtheme-git-");
        if (angular.isArray(flavorName) && typeof(flavorName[1]) !== 'undefined' && flavorName[1] != '') {
          ct.fetchBranches('', flavorName[1]).then(function(r) {
            console.log(r);
            if (typeof r.data.repo !== 'undefined') {
              $s.repository_address = r.data.repo;
            }
            if (typeof r.data.branches !== 'undefined') {
              $s.EditBranchList = r.data.branches;
              $s.showEditBranches = r.data.current_branch;
              $s.path = r.data.path;
              $s.theme_name = r.data.flavor_name;
              $s.flavor = flavorName[1];
            }
          })
        } else {
          $s.errors.push('Please select a valid sub theme');
        }
      } else if(formId.indexOf("delete-subtheme") > -1) {
        $s.deleteScreen = true;
        var flavorName = formId.split("delete-subtheme-");
        if (angular.isArray(flavorName) && typeof(flavorName[1]) !== 'undefined' && flavorName[1] != '') {
          ct.getFlavorName(flavorName[1]).then(function(r) {
            console.log(r);
            if (typeof r.data !== 'undefined') {
              $s.flavor_name = r.data.flavor_name;
              $s.flavor = flavorName[1];
            }
          })
        } else {
          $s.errors.push('Please select a valid sub theme');
        }
      } else {
        $s.themeScreen = true;
      }

      $s.ShowZip = function () {
         $s.zipScreen = $s.zipScreen ? false : true;
         $s.themeScreen = false;
      }
      $s.ShowGit = function () {
         $s.gitScreen = $s.gitScreen ? false : true;
         $s.themeScreen = false;
      }

      $s.uploadFile = function(){
        $s.file = $s.zipThemeUpload;
        $s.name = $s.file['name'];
        promise = ct.uploadZipTheme($s.file, $s.name);
        promise.then(function (r) {
          $s.serverResponse = r;
        }, function () {
            $s.serverResponse = 'An error has occurred';
        })
      };

      $s.fetchBranches = function(){
        if ($s.gitRepo != '') {
          ct.fetchBranches($s.gitRepo, '').then(function(r) {
            console.log(r);
            /* for local */
            if ($s.gitRepo == 'aaa') {
              r.data.branches = {"origin/7.x-1.x":"origin/7.x-1.x", "origin/master":"origin/master"};
            }/**/
            if (typeof r.data.path !== 'undefined') {
              $s.path = r.data.path;
            }
            if (typeof(r.data.branches) !== 'undefined' && r.data.branches !== null) {
              $s.showBranchesSelect = true;
              $s.BranchList = r.data.branches;              
            } else {
              $s.showBranchesSelect = false;
              $s.errors.push('This is an invalid branch');
            }
          })
        }
      };

      $s.editGit = function(){
        ct.editGit($s.showEditBranches, $s.flavor).then(function(result) {
          console.log(result);
          showError(result.data.msg);
        })
      }

      $s.addGit = function(){
        var branchName = $s.showBranches,
            repo = $s.gitRepo;
        ct.submitGit(repo, branchName, $s.path).then(function(result) {
          console.log(result);
          showError(result.data.msg);
        })
      }

      $s.deleteSubtheme = function(){
        ct.deleteTheme($s.flavor).then(function(result) {
          console.log(result);
          showError(result.data.msg);
        })
      }

      showError = function(msg) {
        if (Array.isArray(msg)) {
          if (msg[0] == 'Success') {
            window.location.reload();
          } else{
            for(var i = 0; i < msg.length; i++) {
              $s.errors.push($sce.trustAsHtml(msg[i]));
            }
          }
        }
      }

  }]);

})()
