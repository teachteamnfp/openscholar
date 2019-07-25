(function ($) {

  Drupal.behaviors.views_accordion_faqs = {
    attach: function (context) {
      if (drupalSettings.views_accordion) {
        $.each(drupalSettings.views_accordion, function (id) {
          // Find the set that hasn't been altered yet.
          var $display = $(this.display+' [data-drupal-views-infinite-scroll-content-wrapper] > div.views-row:not(:has(.ui-accordion-header))');
          // The rest of this is copied from views-accordion.js.

          /* The row count to be used if Row to display opened on start is set to random */
          var row_count = 0;

          /* Prepare our markup for jquery ui accordion */
          $(this.header, $display).each(function (i) {
            // Wrap the accordion content within a div if necessary.
            if (!this.usegroupheader) {
              $(this).siblings().wrapAll('<div></div>');
              row_count++;
            }
          });

          if (this.rowstartopen == 'random') {
            this.rowstartopen = Math.floor(Math.random() * row_count);
          }

          // The settings for the accordion.
          var accordionSettings = {
            header: this.header,
            animate: {
              'easing': this.animated,
              'duration': parseInt(this.duration),
            },
            active: this.rowstartopen,
            collapsible: this.collapsible,
            heightStyle: this.heightStyle,
            event: this.event,
            icons: false
          };
          if (this.useHeaderIcons) {
            accordionSettings.icons = {
              'header': this.iconHeader,
              'activeHeader': this.iconActiveHeader
            };
          }

          /* jQuery UI accordion call */
          $display.accordion(accordionSettings);
        })
      }
    }
  }

})(jQuery);