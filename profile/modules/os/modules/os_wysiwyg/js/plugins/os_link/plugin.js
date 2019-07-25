(function ($) {

  CKEDITOR.plugins.add('os_link', {
    requires: '',
    lang: 'en,en-au,en-ca,en-gb',
    icons: 'oslink',
    init: function (editor) {
      let self = this;
      editor.addCommand('os_wysiwyg_link', {
        exec: function (editor) {
          let selection = editor.getSelection(),
            element = self.getLinkFromSelection(selection),
          data = {};

          if (element) {
            self.selectLink(selection, element);
            data = self.parseAnchor(element)
          }
          else {
            data = {
              text: self.getTextFromSelection(selection),
              url: '',
              type: '',
              title: '',
              newWindow: false
            };
          }

          Drupal.wysiwyg.osLink.modal(editor, data, self.insertLink)
        },
        context: 'a[href]',
        allowedContent: 'a[!href,target,data-url,data-mid]',
        requiredContent: 'a[href]'
      });

      editor.ui.addButton('OsLink', {
        label: 'Link',
        command: 'os_wysiwyg_link',
        toolbar: 'links,10'
      });
    },
    parseAnchor: function (a) {
      let output = {
        text: '',
        url: '',
        type: '',
        title: '',
        newWindow: false
      };

      if (!a || a.constructor.name != 'HTMLAnchorElement') {
        return output;
      }

      output.text = a.innerHTML;
      if (a.hasAttribute('data-mid')) {
        output.url = a.getAttribute('data-mid');
        output.type = 'file';
      }
      else if (a.origin == 'mailto://' || a.protocol == 'mailto:') {
        output.url = a.pathname || a.href.replace('mailto:', '');
        output.type = 'email';
      }
      else {
        let home = drupalSettings.path.baseUrl + (drupalSettings.path.pathPrefix),
          dummy = document.createElement('a');
        dummy.href = home;
        // TODO: Remove the 0 when internal is implemented
        if (0 && dummy.hostname == a.hostname && a.pathname.indexOf(dummy.pathname) != -1) {
          // internal link
          output.url = a.pathname.replace(home, '');
          output.type = 'internal';
        }
        else if (a.hasAttribute('data-url')) {
          output.url = a.getAttribute('data-url');
          output.type = 'url';
        }
        else {
          output.url = a.href.replace(home, '');
          output.type = 'url';
        }
      }

      if (a.hasAttribute('title')) {
        output.title = a.getAttribute('title');
      }

      if (a.getAttribute('target')) {
        output.newWindow = true;
      }

      return output;
    },
    insertLink: function (editor, body, target, attributes) {
      let html = '<a href="'+target+'">'+(body?body:target)+'</a>';

      if (attributes) {
        let $html = $(html);
        $html.attr(attributes);
        html = typeof $html[0].outerHTML != 'undefined'
          ? $html[0].outerHTML
          : $html.wrap('<div>').parent().html();
      }
      editor.insertHtml(html);
    },
    getLinkFromSelection: function (selection) {
      var node = selection.getStartElement().$;
      while (node.nodeName != 'A') {
        if (node.nodeName == 'BODY') {
          return null;
        }
        node = node.parentNode;
      }
      return node;
    },
    getTextFromSelection: function (selection) {
      return selection.getSelectedText();
    },
    selectLink: function (selection, node) {
      selection.selectElement(new CKEDITOR.dom.element(node));
    }
  });

  // This function is populated later by an angular module.
  //
  Drupal.wysiwyg = Drupal.wysiwyg || {};
  Drupal.wysiwyg.osLink = Drupal.wysiwyg.osLink || {
    modal: function () {}
  };

})(jQuery);