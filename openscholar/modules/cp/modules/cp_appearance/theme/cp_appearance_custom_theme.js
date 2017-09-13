(function () {

  var m = angular.module('CpAppearanceCustomTheme', ['angularModalService', 'formElement', 'os-buttonSpinner']);

    /**
   * Fetches the content settings forms from the server and makes them available to directives and controllers
   */
  m.service('customTheme', ['$http', '$q', function ($http, $q) {

    var baseUrl = Drupal.settings.paths.api;
    var uploadUrl = baseUrl+'/themes/';
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
      $http.post(uploadUrl + 'abcd/', file, config, {
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

    this.fetchBranches = function(gitBranchName){
      var vals = {
        git: gitBranchName
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
      return $http.put(uploadUrl, vals).then(function (r) {
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
             '<div class="git-screen" ng-show = "gitScreen">' +
             '<label for="edit-repository">Git repository address <span class="form-required" title="This field is required.">*</span></label>'+
             '<input type="text" name="repository" ng-model="gitRepo" value="" size="60" maxlength="128" ng-model="gitAddress">'+
             '<div id="branches-wrapper"><div class="form-actions form-wrapper" id="edit-actions"><a href="#" ng-click="fetchBranches()">Fetch branches</a></div></div>'+
             '<div class="custom-theme-help-link"><a href="https://docs.openscholar.harvard.edu/subsite-themes"  target="_blank">Learn more about Subsite Themes</a></div>'+
             '<div class="form-item form-type-select form-item-branch" ng-show="showBranchesSelect">' +
             '<label for="edit-branch">Branch <span class="form-required" title="This field is required.">*</span></label>' +
             '<select name="branch" ng-model="showBranches" class="form-select required" ng-options="key as value for (key , value) in BranchList"><option value="" selected="selected">- Select -</option></select>' +
             '<div class="description">Enter the branch of the git repository</div></div>' +
             '<div class="actions"><button type="submit" button-spinner="settings_form" spinning-text="Saving" ng-click="addGit()">Save</button><input type="button" value="Close" ng-click="close(false)"></div></form></div></form>'+
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
  m.controller('customThemeController', ['$scope', '$sce', 'customTheme', function ($s, $sce, ct) {

      $s.gitScreen = false;
      $s.zipScreen = false;
      $s.themeScreen = true;
      $s.showBranchesSelect = false;
      $s.file = {};
      $s.path = '';
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
        promise.then(function (response) {
          $s.serverResponse = response;
        }, function () {
            $s.serverResponse = 'An error has occurred';
        })
      };

      $s.fetchBranches = function(){
        if ($s.gitRepo != '') {
          ct.fetchBranches($s.gitRepo).then(function(response) {
            if ($s.gitRepo == 'aaa') {
              response.data.branches = {"origin/7.x-1.x":"origin/7.x-1.x", "origin/master":"origin/master"};
            }
            if (response.data.branches !== null) {
              $s.showBranchesSelect = true;
            } else {
              $s.showBranchesSelect = false;
            }
            if (response.data.path != '') {
              $s.path = response.data.path;
            }
            $s.BranchList = response.data.branches;
          })
        }
      };

      $s.addGit = function(){
        var branchName = $s.showBranches,
            repo = $s.gitRepo;
        ct.submitGit(repo, branchName, $s.path).then(function(result) {
          console.log(result);
        })
      }
  }]);

})()
