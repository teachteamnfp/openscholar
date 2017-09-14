Feature:
  Testing the events (calendar) tab.

  @api @features_first
  Scenario: Test the Management of registrations for an event with no registrants
    Given I am logging in as "john"
     When I create a new registration event with title "Registration event"
      And I manage registrations for the event "Registration event"
      And I should not see "Download Registrant List"

  @api @features_first
  Scenario: Test the Management of registrations for an event with a registrant
    Given I am logging in as "john"
      And I sign up "Foo bar" with email "foo@example.com" to the event "Registration event"
      And I manage registrations for the event "Registration event"
      And text exists "Foo bar"
     Then I click "Download Registrant List"
     Then I should see "Simple view"
      And I should see "CSV"

  @api @features_first
  Scenario: Test the simple view for the event registrants
    Given I am logging in as "john"
      And I manage registrations for the event "Registration event"
     When I click "Simple view"
     Then I should see "[ ] Foo bar - foo@example.com"

  @api @features_first
  Scenario: Test the simple view for the event registrants
    Given I am logging in as "john"
      And I manage registrations for the event "Registration event"
     When I export the registrants list for the event "Registration event" in the site "john"
     Then I verify the file contains the user "Foo bar" with email of "foo@example.com"

  @api @features_first @os_events	@create_repeating_event_that_stops_after_a_number_of_occurences @javascript
  Scenario: Create repeating event that stops after a number of occurences
    Given I am logging in as "john"
      And I visit "john/calendar"
      And I visit "john/node/add/event"
      And I fill in "Title" with "Cabinet meeting"
      And I check the box "Repeat"
      And I select "Daily" from "Repeats"
      And I select the radio button "After occurrences" with the id "edit-field-date-und-0-rrule-range-of-repeat-count"
      And I fill in "edit-field-date-und-0-rrule-count-child" with "3"
      And I press "Save"
     Then I should see "Event Cabinet meeting has been created"
      And I click "CALENDAR"
     Then I should see "3" events named "Cabinet meeting" over the next "1" pages

  @api @features_first @os_events	@create_repeating_event_that_stops_on_a_particular_date @javascript
  Scenario: Create repeating event that stops on a particular date
    Given I am logging in as "john"
      And I visit "john/calendar"
      And I visit "john/node/add/event"
      And I fill in "Title" with "Daily brief"
      And I check the box "Repeat"
      And I select "Daily" from "Repeats"
      And I select the radio button On Until Date E.g., "M d Y" with the id "edit-field-date-und-0-rrule-range-of-repeat-until"
      And I fill in "edit-field-date-und-0-rrule-until-child-datetime-datepicker-popup-0" with date interval "P7D" from "now"
      And I press "Save"
     Then I should see "Event Daily brief has been created"
      And I click "CALENDAR"
     Then I should see "8" events named "Daily brief" over the next "1" pages

  @api @features_first @os_events	@create_repeating_event_that_excludes_a_particular_date
  Scenario: Create repeating event that excludes a particular date
    Given I am logging in as "john"
      And I visit "john/calendar"
      And I visit "john/node/add/event"
      And I fill in "Title" with "Daily intelligence briefing"
      And I check the box "Repeat"
      And I select "Daily" from "Repeats"
      And I select the radio button On Until Date E.g., "M d Y" with the id "edit-field-date-und-0-rrule-range-of-repeat-until"
      And I fill in "edit-field-date-und-0-rrule-until-child-datetime-datepicker-popup-0" with date interval "P7D" from "now"
      And I check the box "Exclude dates"
      And I fill in "edit-field-date-und-0-rrule-exceptions-exdate-0-datetime-datepicker-popup-0" with date interval "P3D" from "now"
      And I press "Save"
     Then I should see "Event Daily intelligence briefing has been created"
      And I click "CALENDAR"
     Then I should see "14" events named "Daily intelligence briefing" over the next "1" pages
      And I should "see" event named "Daily intelligence briefing" on date "P2D" from "now" over the next "1" pages
      And I should "not see" event named "Daily intelligence briefing" on date "P3D" from "now" over the next "1" pages
      And I should "see" event named "Daily intelligence briefing" on date "P4D" from "now" over the next "1" pages

  @api @features_first @os_events	@create_repeating_event_that_includes_a_particular_date
  Scenario: Create repeating event that includes a particular date
    Given I am logging in as "john"
      And I visit "john/calendar"
      And I visit "john/node/add/event"
      And I fill in "Title" with "Press briefing"
      And I check the box "Repeat"
      And I select "Daily" from "Repeats"
      And I select the radio button "After occurrences" with the id "edit-field-date-und-0-rrule-range-of-repeat-count"
      And I fill in "edit-field-date-und-0-rrule-count-child" with "4"
      And I check the box "Include dates"
      And I fill in "edit-field-date-und-0-rrule-additions-rdate-0-datetime-datepicker-popup-0" with date interval "P7D" from "now"
      And I press "Save"
     Then I should see "Event Press briefing has been created"
      And I click "CALENDAR"
     Then I should see "10" events named "Press briefing" over the next "1" pages
      And I should "see" event named "Press briefing" on date "P3D" from "now" over the next "1" pages
      And I should "not see" event named "Press briefing" on date "P4D" from "now" over the next "1" pages
      And I should "see" event named "Press briefing" on date "P7D" from "now" over the next "1" pages

  @api @features_first @os_events	@limit_number_of_registrants_for_an_event @javascript
  Scenario: Limit number of registrants for an event
    Given I am logging in as "john"
      And I visit "john/calendar"
      And I visit "john/node/add/event"
      And I fill in "Title" with "State dinner"
      And I fill in "edit-field-date-und-0-value-datepicker-popup-0" with date interval "P3D" from "now"
      And I check the box "Signup"
      And I press "Save"
      And I click "Manage Registrations"
      And I click "Settings"
      And I fill in "Capacity" with "1"
      And I press "Save Settings"
      And I visit the unaliased registration path of "event/state-dinner" on vsite "john" and append "0"
      And I turn off Mollom CAPTCHA verification
      And I fill in "Email" with "khrushchev@kremlin.ru"
      And I fill in "Full name" with "Nikita Khrushchev"
#     And I fill in "Verification" with "correct"
      And I press "Signup"
     Then I should see "Sorry, the event is full"
