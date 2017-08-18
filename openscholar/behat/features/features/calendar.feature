Feature: Testing OpenScholar calendar page.

  @api @features_first @calendar @test_the_calendar_tab
  Scenario: Test the Calendar tab
    Given I visit "john"
     When I click "Calendar"
     Then I should see "Testing event"
      And I should see the link "Export" under "view-os-events"

  @api @features_first @calendar @adding_vocabulary_for_events_content
  Scenario: Adding vocabulary for events content
    Given I am logging in as "john"
      And I create the vocabulary "event type" in the group "john" assigned to bundle "event"
      And I visit "john/cp/build/taxonomy/eventtype/add"
      And I fill in "Name" with "astronomy"
      And I check the box "Generate automatic URL alias"
     When I press "edit-submit"
     Then I verify the alias of term "astronomy" is "john/event-type/astronomy"

  @api @features_first @calendar @adding_term_for_existing_events_content
  Scenario: Adding term for existing events content
    Given I am logging in as "john"
      And I visit "john/cp/build/taxonomy/eventtype/add"
      And I fill in "Name" with "birthday"
      And I check the box "Generate automatic URL alias"
     When I press "edit-submit"
     Then I verify the alias of term "astronomy" is "john/event-type/astronomy"

	@api @features_first @calendar @create_new_event_and_assign_a_term_to_it
	Scenario: Create new event and assign a term to it
    Given I am logging in as "john"
      And I create an upcoming event with title "Someone" in the site "john"
      And I assign the node "Someone" with the type "event" to the term "birthday"
     Then I should get a "200" HTTP response

  @api @features_first @calendar @assigning_terms_to_events
  Scenario: Assigning terms to events
    Given I am logging in as "john"
      And I assign the node "John F. Kennedy birthday" with the type "event" to the term "birthday"
      And I assign the node "Halleys Comet" with the type "event" to the term "astronomy"
     Then I should get a "200" HTTP response

  @api @features_first @calendar @test_the_'day'_calendar_tab
  Scenario: Test the 'Day' Calendar tab
    Given I visit "john/calendar?type=day&day=2020-05-29"
     Then I should see the text "John F. Kennedy birthday" under "view-display-id-page_1"
      And I should not see the link "Export" under "view-os-events"
     When I visit "john/calendar/event-type/birthday?type=day&day=2020-05-29"
     Then I should see the text "John F. Kennedy birthday" under "view-display-id-page_1"
      And I should not see the link "Export" under "view-os-events"
     When I visit "john/calendar/event-type/astronomy?type=day&day=2020-05-29"
     Then I should not see the text "John F. Kennedy birthday" under "view-display-id-page_1"

  @api @features_first @calendar @test_the_'week'_calendar_tab
  Scenario: Test the 'Week' Calendar tab
    Given I visit "john/calendar?week=2020-W22&type=week"
     Then I should see the text "John F. Kennedy birthday" under "view-display-id-page_1"
      And I should not see the link "Export" under "view-os-events"
     When I visit "john/calendar/event-type/birthday?week=2020-W22&type=week"
     Then I should see the text "John F. Kennedy birthday" under "view-display-id-page_1"
      And I should not see the link "Export" under "view-os-events"
     When I visit "john/calendar/event-type/astronomy?week=2020-W22&type=week"
     Then I should not see the text "John F. Kennedy birthday" under "view-display-id-page_1"

  @api @features_first @calendar @test_the_'month'_calendar_tab
  Scenario: Test the 'Month' Calendar tab
    Given I visit "john/calendar?type=month&month=2020-05"
     Then I should see the link "John F. Kennedy birthday" under "view-display-id-page_1"
      And I should not see the link "Export" under "view-os-events"
     When I visit "john/calendar/event-type/birthday?type=month&month=2020-05"
     Then I should see the link "John F. Kennedy birthday" under "view-display-id-page_1"
      And I should not see the link "Export" under "view-os-events"
     When I visit "john/calendar/event-type/astronomy?type=month&month=2020-05"
     Then I should not see the text "John F. Kennedy birthday" under "view-display-id-page_1"

  @api @features_first @calendar @test_the_'year'_calendar_tab
  Scenario: Test the 'Year' Calendar tab
    Given I visit "john/calendar?type=year&year=2020"
     Then I should see the link "29" under "has-events"
      And I should not see the link "Export" under "view-os-events"
     When I visit "john/calendar/event-type/birthday?type=year&year=2020"
     Then I should see the link "29" under "view-display-id-page_1"
      And I should not see the link "Export" under "view-os-events"
     When I visit "john/calendar/event-type/astronomy?type=year&year=2020"
     Then I should not see the link "29" under "view-display-id-page_1"

  @api @features_first @calendar @testing_the_past_events_list
  Scenario: Testing the Past Events list
    Given I visit "john/calendar/past_events"
     Then I should see the link "Past event" under "view-os-events"
      And I should not see the link "Export" under "view-os-events"

  @api @features_first @calendar @testing_the_upcoming_events_list
  Scenario: Testing the Upcoming Events list
    Given I visit "john/calendar/upcoming"
     Then I should see the link "Future event" under "view-os-events"
      And I should not see the link "Past event" under "view-os-events"
     When I click on link "iCal" under "content"
     Then I should find the text "SUMMARY:Halleys Comet" in the file
      And I should not find the text "Past event" in the file

  @api @features_first @calendar @testing_the_upcoming_events_list_limited_by_term
  Scenario: Testing the Upcoming Events list limited by term
    Given I visit "john/calendar/upcoming/event-type/birthday"
     Then I should see the link "Someone" under "view-os-events"
      And I should not see the link "Halleys Comet" under "view-os-events"
     When I click on link "iCal" under "content"
     Then I should find the text "SUMMARY:Someone" in the file
      And I should not find the text "SUMMARY:Halleys Comet" in the file

  @api @features_first @calendar @testing_the_single_event_export_in_ical_format.
  Scenario: Testing the single event export in iCal format.
    Given I visit "john/event/testing-event"
     When I click on link "iCal" under "content"
     Then I should find the text "SUMMARY:Testing event" in the file
      And I should not find the text "SUMMARY:John F. Kennedy birthday" in the file
      And I should not find the text "SUMMARY:Halleys Comet" in the file

  @api @features_first @calendar @test_that_site-wise_calendar_is_disabled
  Scenario: Test that site-wise calendar is disabled
     Given I go to "calendar"
      Then I should get a "403" HTTP response

  @api @features_first @calendar @test_the_next_week_tab
  Scenario: Test the next week tab
    Given I visit "john/calendar"
      And I click "Week"
      And I click "Navigate to next week"
     Then I should verify the next week calendar is displayed correctly

  @api @features_first @calendar @test_create_event_content @eam_1229
  Scenario: Test create event content
    Given I am logging in as "john"
      And I visit "john/node/add/event"
      And I sleep for "3"
      And I fill in "Title" with "Inaugural address"
      And I fill in "Body" with "And so, my fellow Americans: ask not what your country can do for you; ask what you can do for your country."
      And I fill in "Location" with "Washington D.C."
      And I press "Save"
     Then I should see "Inaugural address"
      And I should match the regex "(Sun|Mon|Tue|Wed|Thu|Fri|Sat)\w*,\s+(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)\w*\s+\d+,\s*((19|20){0,1}\d\d)"
      And I should match the regex "Location:.*Washington D.C."
      And I should find the text "ask not what your country can do for you"

  @api @features_first @calendar @test_create_event_content @eam_1229
  Scenario: Test edit event content
    Given I am logging in as "john"
      And I visit the unaliased edit path of "event/inaugural-address" on vsite "john"
      And I sleep for "3"
      And I fill in "Title" with "Presidential inaugural address"
      And I fill in "Body" with "My fellow citizens of the world: ask not what America will do for you, but what together we can do for the freedom of man."
      And I fill in "Location" with "District of Columbia"
      And I press "Save"
      And I sleep for "3"
     Then I should see "Presidential inaugural address"
      And I should match the regex "(Sun|Mon|Tue|Wed|Thu|Fri|Sat)\w*,\s+(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)\w*\s+\d+,\s*((19|20){0,1}\d\d)"
      And I should match the regex "Location:.*District of Columbia"
      And I should find the text "ask not what America will do for you"
