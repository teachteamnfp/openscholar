(function() {
  var app = angular.module('myApp', []);
  app.controller('FormCtrl', function ($scope, $http) {
      
      $scope.data = {
          firstname: "default",
          emailaddress: "default",
          gender: "default",
          member: false,
          file_profile: "default",
          file_avatar: "default"
      };
      $scope.submitForm = function() {
          console.log("posting data....");
          $http.post('/cp/users/force-harvard-key-login', JSON.stringify(data)).success(function() {

            /*success callback*/

          });
      };
  });
})();
