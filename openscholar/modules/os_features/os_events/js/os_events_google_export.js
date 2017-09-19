(function ($) {

  /**
   * Display dialog to choose between overwriting or creating a new exported Google Calendar.
   */
  Drupal.behaviors.osPromptForOverwriteOrCreateGoogleCalendar = {
    attach: function () {

      function osEventsDialogButton(overwrite, label) {

        var throbber = $("#export-to-google-calendar-throbber").dialog({
          resizable: false,
          height: "auto",
          width: 600,
          modal: true,
          show: false,
          hide: true,
          autoOpen: false,
        });

        this.button = {
          text: label,
          click: function () {
            var $this = $(this);
            $.ajax({
              url: document.location.pathname + '?overwrite=' + overwrite,
              type: 'POST',
              success: function (resp) {
                alert("Calendar export complete");
                throbber.dialog("close");
                $this.dialog("close");
              },
              beforeSend: function (xhr, settings) {
                throbber.dialog("open");
                $this.dialog("close");
              },
              complete: function () {
                $this.dialog("close");
                throbber.dialog("close");
              },
              error: function (resp) {
                alert(JSON.stringify(resp));
              }
            });
            $(this).dialog("close");
          }
        };
      }

      var button1 = new osEventsDialogButton(1, "Create New Google Calendar");
      var button2 = new osEventsDialogButton(0, "Overwrite Existing Google Calendar");

      $("#export-to-google-calendar-throbber").once("export-to-google-calendar-throbber", function () {

        $("#export-to-google-calendar-dialog-confirm").dialog({
          resizable: false,
          height: "auto",
          width: 600,
          modal: true,
          buttons: [
            button1.button,
            button2.button,
            {
              text: "Cancel",
              click: function () {
                $(this).dialog("close");
              }
            }
          ]
        });
      });

    }
  };

})(jQuery);
