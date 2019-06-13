(function () {

  CKEDITOR.plugins.add('media_browser', {
    lang: 'en,en-au,en-ca,en-gb',
    icons: 'media_browser',
    hidpi: false, // If set to true, there must be files in the icons/hidpi directory,
    init: function (editor) {
      editor.addCommand('media_browser', {
        exec: function (editor) {
          let selection = editor.getSelection(),
              element = selection.getSelectedElement();

          if (element) {

          }
          else {
            Drupal.media.modal({
              onSelect: function (e) {
                for (let i = 0; i < e.length; i++) {
                  let media = e[i];
                  editor.insertHtml('<img src="' + media.thumbnail +'" data-mid="' + media.mid + '">');
                }
              }
            })
          }
        },
        context: 'img[data-mid]',
        allowedContent: 'img[!src,alt,width,height,!data-mid]',
        requiredContent: 'img[src,data-mid]'
      });

      editor.ui.addButton('MediaBrowser', {
        label: 'Embed Media',
        command: 'media_browser',
        toolbar: 'insert,10'
      });
    }
  });

})();