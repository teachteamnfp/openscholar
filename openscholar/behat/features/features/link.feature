Feature:
  Testing the link tab.

  @api @features_second
  Scenario: Test the Links tab
    Given I visit "john"
     When I click "Links"
     Then I should see "JFK wikipedia page"

  @api @features_first
  Scenario: Create new link content
     Given I am logging in as "john"
        And I visit "john/node/add/link"
       When I fill in "Title" with "Google"
       When I fill in "edit-field-links-link-und-0-url" with "https://www.google.com"
        And I press "Save"
        And I sleep for "2"
       Then I should see "Google"

  @api @features_first
  Scenario: Edit link content
     Given I am logging in as "john"
        And I visit the unaliased edit path of "links/google" on vsite "john"
       When I fill in "Title" with "Google_one"
       When I fill in "edit-field-links-link-und-0-url" with "https://www.google.com"
        And I press "Save"
        And I sleep for "2"
       Then I should see "Google_one"

  @api @features_first
  Scenario: Delete link content
     Given I am logging in as "john"
        And I visit the unaliased edit path of "links/google" on vsite "john"
      When I click "Delete this link"
      Then I should see "This action cannot be undone."
       And I press "Delete"
      Then I should see "has been deleted"