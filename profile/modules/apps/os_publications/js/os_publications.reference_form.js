/**
 * @file
 * Reference/Publication form customizations for OpenScholar.
 */

(function ($, Drupal) {
    "use strict";

    Drupal.behaviors.osPublicationsForm = {
        attach: function (context, settings) {
            // Handle year fields.
            let codedYear = $("input[name='bibcite_year_coded']");
            let yearField = $("input[name='bibcite_year[0][value]']");

            // Add validation warning.
            let yearWarning = $("#bibcite-year-group-validate");
            if (!yearField.hasClass('error')) {
                yearWarning.css('visibility', 'hidden');
            }
            yearWarning.css('color', 'red');

            // Allowed year input.
            let numbers = /^[0-9]+$/;

            // Publication year can be either given in a numerical value or by a coded
            // value ("in press", "submitted" and so on). If the user fills a numerical
            // value it is validated and shows warning message.
            let validate = !codedYear.val();
            yearField.keyup(function() {
                if (this.value !== '' && validate) {

                    // Validate year input.
                    let userInput = this.value;
                    if ((userInput.length !== 4 && userInput.match(numbers)) || !userInput.match(numbers)) {
                        yearWarning.css('visibility', 'visible');
                        yearField.addClass("error");
                    }
                    else if (userInput.length === 4 && userInput.match(numbers)) {
                        yearWarning.css('visibility', 'hidden');
                        yearField.removeClass("error");
                    }
                }
                else {
                    yearWarning.css('visibility', 'hidden');
                    yearField.removeClass("error");
                }
            });
            codedYear.change(function() {
                if (this.value !== 0) {
                    // Empty the year field.
                    yearField.val('');
                    // Empty month field.
                    let monthField = "#edit-publication-month";
                    if ($(monthField).length) {
                        $(monthField).val('_none');
                    }
                    // empty Day field.
                    let dayField = "#edit-publication-day";
                    if ($(dayField).length) {
                        $(dayField).val('_none');
                    }
                }
                if (this.value === "0") {
                    validate = true;
                }
                else {
                    validate = false;
                }
            });
        }
    };

})(jQuery, Drupal);
