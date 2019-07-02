(function () {

  var m = angular.module('OsWysiwygLinkTool', ['EntityService', 'FileHandler', 'FileEditor', 'JSPager', 'angularModalService']);

  m.service('OWLModal', ['ModalService', function (ModalService) {
    var dialogParams = {
      buttons: {},
      dialogClass: 'wysiwyg-link-tool-wrapper',
      modal: true,
      draggable: false,
      resizable: false,
      minWidth: 600,
      width: 800,
      position: 'center',
      title: undefined,
      overlay: {
        backgroundColor: '#000000',
        opacity: 0.4
      },
      zIndex: 10000,
      close: function (event, ui) {
        $(event.target).remove();
      }
    };

    return {
      open: function (text, type, urlArgument, titleText, newWindow, close) {
        ModalService.showModal({
          templateUrl: Drupal.settings.paths.owl + '/theme/OsWysiwygLinkTool.template.html',
          controller: 'OWLModalController',
          inputs: {
            params: {
              text: text,
              type: type,
              arg: urlArgument,
              title: titleText,
              newWindow: newWindow,
            }
          }
        })
        .then (function (modal) {
          modal.element.dialog(dialogParams);
          modal.close.then(function(result) {
            if (angular.isFunction(close)) {
              close(result);
            }
          });
        })
      }
    }
  }]);

  m.controller('OWLModalController', ['$scope', 'EntityService', 'FileHandlers', 'params', 'close', function ($s, EntityService, FileHandlers, params, close) {
    var files = new EntityService('files', 'id');
    var editting = false;
    var extensions = [];
    for (var k in Drupal.settings.extensionMap) {
      extensions = extensions.concat(Drupal.settings.extensionMap[k]);
    }

    $s.fh = FileHandlers.getInstance(
      extensions,
      Drupal.settings.maximumFileSize,
      Drupal.settings.maximumFileSizeRaw,
      function ($files) {
        for (var i = 0; i < $files.length; i++) {
          files.register($files[i]);
        }

        if ($files.length == 1) {
          $s.arg = $files[0].id;
          editing = true;
        }
    });

    $s.extensionStr = extensions.join(', ');
    $s.filesize = Drupal.settings.maxFileSize;

    $s.text = params.text;
    $s.arg = params.arg;
    $s.title = params.title;
    $s.newWindow = params.newWindow;
    $s.active = params.type || 'url';

    let param = {};

    $s.ready = false;
    files.fetch(param).then(function (result) {
      $s.files = result;
      $s.ready = true;
    }, function (e) {
      console.log(e);
      $s.error = true;
    });

    $s.setLinkTarget = function (arg) {
      $s.arg = arg;
    };

    $s.close = function (insert) {
      let ret = {
        type: $s.active,
        arg: $s.arg,
        text: $s.text,
        title: $s.title,
        newWindow: $s.newWindow,
        insert: insert
      };

      close(ret);
    }
  }]);

  m.run(['OWLModal', '$timeout', function (modal, $t) {

    function replacement(editorId, info, callback) {
      function closeHandler(linkInfo) {
        console.log(linkInfo);

        if (linkInfo.insert) {
          let body = linkInfo.text,
            target = '',
            attributes = {};

          switch (linkInfo.type) {
            case 'url':
              target = linkInfo.arg;
              attributes['data-url'] = linkInfo.arg;
              break;
            case 'email':
              target = 'mailto:' + linkInfo.arg;
              break;
            case 'file':
              target = linkInfo.arg;
              attributes['data-fid'] = linkInfo.arg;
              break;
          }

          if (linkInfo.newWindow) {
            attributes.target = '_blank';
          }

          if (linkInfo.title) {
            attributes.title = linkInfo.title;
          }

          callback(editorId, body, target, attributes);
        }
      }
      modal.open(info.text, info.type, info.url, info.title, info.newWindow, closeHandler)
    }

    try {
      Drupal.wysiwyg.plugins.os_link.openModal = replacement;
    }
    catch (e) {
      console.log("Attempting to access plugin too early.");
    }
  }]);

})();