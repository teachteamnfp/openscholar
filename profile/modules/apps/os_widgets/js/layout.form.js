(function ($) {
  let bound = false;

  Drupal.behaviors.osWidgetsLayoutForm = {
    attach: function (context, settings) {
      let all_contexts = drupalSettings.layoutContexts,
        top_level = (new URLSearchParams(window.location.search)).get('context'),
        active_limited = [],
        context_found = !top_level;

      for (let k in all_contexts) {
        if (k == top_level) {
          context_found = true;
        }
        if (context_found) {
          active_limited.push(k);
        }
      }

      if (top_level) {
        $('#block-place-context-selector', context).val(top_level);
      }

      // swap the context that is being editted.
      $('#block-place-context-selector', context).change(function (e) {
        let new_context = e.target.value;

        vars = parseQuery();
        vars.context = new_context;
        let query_string = $.param(vars);
        window.location.search = '?' + query_string;
      });

      // Hide widgets if the title doesn't match.
      $('#filter-widgets', context).keyup(function (e) {
        let str = e.target.value.toLowerCase();
        $('#block-list .block', context).each(function (i) {
          $this = $(this);
          if ($this.find('.block-title').text().toLowerCase().indexOf(str) != -1) {
            $this.show();
          }
          else {
            $this.hide();
          }
        });
      });

      // Define regions to be sortable targets
      $('.block-place-region', context).sortable({
        items: '> div, > nav',
        connectWith: '.block-place-region, #block-list',
        placeholder: 'ui-state-drop',
        appendTo: document.body,
        helper: 'clone',
        scroll: false,
        out: function (e, ui) {
          console.log(ui);
        }
      });

      // Define the unused widget list to be a sortable target
      $('#block-list', context).sortable({
        items: '> div, > nav',
        connectWith: '.block-place-region, #block-list',
        appendTo: document.body,
        helper: 'clone',
        scroll: false,
        out: function (e, ui) {
          console.log(ui);
        }
      });

      // Open the new widget panel
      $('#create-new-widget-btn', context).click(function (e) {
        $('#factory-wrapper').show();
      });

      $('#factory-wrapper .close', context).click(function (e) {
        $('#factory-wrapper').hide();
      });

      // Submit the layout to the server.
      $('#block-place-actions-wrapper .btn-success', context).click(function (e) {
        let items = {};
        $('.block-place-region').each(function (region) {
          let region_name = this.attributes['data-region'].value;
          $(this).find('> div, > nav').each(function (weight) {
            if (this.attributes['data-block-id']) {
              let id = this.attributes['data-block-id'].value;
              items[id] = {
                id: id,
                region: region_name,
                weight: weight
              };
            }
          });
        });

        let payload = {
          contexts: active_limited,
          blocks: items
        };
        let url = Drupal.url(drupalSettings.path.layout.saveLayout);
        $.post(url, payload).done(function (data, status, xhr) {
          vars = parseQuery();
          delete vars.context;
          delete vars['block-place'];
          delete vars.destination;
          let query_string = $.param(vars);
          window.location.search = '?' + query_string;
        }).fail(function (xhr, status, error) {

        });
        e.target.disabled = true;
      });

      $('#block-place-actions-wrapper button[value="Reset"]', context).click(function (e) {
        let payload = {
          contexts: active_limited
        };
        let url = Drupal.url(drupalSettings.path.layout.resetLayout);
        $.post(url, payload).done(function (data, status, xhr) {
          window.location.reload();
        }).fail(function (xhr, status, error) {
          console.log('Reset failed with the error: '+error.error_message);
        });
      });

      if (!bound) {
        $(window).bind('dialog:beforecreate', function (event, dialog, $element, settings) {
          // If this attribute exists on a modal when we attempt to reuse it, the modal will have most of its
          //   inner markup removed and will be completely useless.
          // @see https://www.drupal.org/project/bootstrap/issues/3032922
          $element.removeAttr('data-drupal-theme');
        });
        bound = true;
      }

      $('a[data-drupal-selector="edit-cancel"]', context).click(function (e) {
        e.preventDefault();
        e.stopPropagation();

        $('#drupal-modal').dialog('close');
      });
    }
  };

  Drupal.behaviors.layoutBuilderDisableInteractiveElements = {
    attach: function attach(context, settings) {
      let $blocks = $('#layout-builder [data-layout-block-uuid]', context);
      $blocks.find('input, textarea, select').prop('disabled', true);
      $blocks.find('a').not(function (index, element) {
        return $(element).closest('[data-contextual-id]').length > 0;
      }).on('click mouseup touchstart', function (e) {
        e.preventDefault();
        e.stopPropagation();
      });

      $blocks.find('button, [href], input, select, textarea, iframe, [tabindex]:not([tabindex="-1"]):not(.tabbable)').not(function (index, element) {
        return $(element).closest('[data-contextual-id]').length > 0;
      }).attr('tabindex', -1);
    }
  };

  /**
   * Parse the query arguments into an object map
   * @returns {{}}
   */
  function parseQuery() {
    let vars = {}, hash;

    let q = window.location.search;
    if (q != undefined) {
      q = q.slice(1).split('&');
      for (let i = 0; i < q.length; i++) {
        hash = q[i].split('=');
        vars[hash[0]] = hash[1];
      }
    }

    return vars;
  }

})(jQuery);