(function ($) {

  /**
   * Display dialog to choose between overwriting or creating a new exported Google Calendar.
   */
  Drupal.behaviors.osPromptForOverwriteOrCreateGoogleCalendar = {
    attach: function () {
      $( "#dialog-confirm" ).dialog({
        resizable: false,
        height: "auto",
        width: 400,
        modal: true,
        buttons: {
          "Overwrite Existing Google Calendar": function() {
            $( this ).dialog( "close" );
          },
          "Create New Google Calendar": function() {
            $( this ).dialog( "close" );
          },
          Cancel: function() {
            $( this ).dialog( "close" );
          }
        }
      });
    }
  }

})(jQuery);
