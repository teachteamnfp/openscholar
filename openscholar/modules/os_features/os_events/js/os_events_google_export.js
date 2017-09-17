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
             $.ajax({
                url     : document.location.pathname + '?overwrite=1',
                type    : 'POST',
                data    : '{overwrite:1}',
                success : function(resp) {
                  alert("Calendar export complete");
                  $(this).dialog( "close" );
                },
                error   : function(resp){
                  alert(JSON.stringify(resp));
                }
             });
            $( this ).dialog( "close" );
          },
          "Create New Google Calendar": function() {
             $.ajax({
                url     : document.location.pathname + '?overwrite=0',
                type    : 'POST',
                data    : '{overwrite:0}',
                success : function(resp) {
                  alert("Calendar export complete");
                  $(this).dialog( "close" );
                },
                error   : function(resp){
                  alert(JSON.stringify(resp));
                }
             });
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
