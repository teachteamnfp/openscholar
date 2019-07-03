(function () {

  CKEDITOR.plugins.add('os_wysiwyg_link', {
    requires: '',
    lang: 'en,en-au,en-ca,en-gb',
    icons: 'os_link',
    init: function (editor) {
      editor.addCommand('os_wysiwyg_link', {
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
      })
    }
  });

})();