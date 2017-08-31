Feature:
  Testing the news tab.

  @api @features_second
  Scenario: Test the News tab
    Given I visit "john"
     When I click "News"
      And I click "I opened a new personal"
     Then I should see "This is a new site generated via the vsite options in open scholar."

  @api @features_first @news_content
  Scenario: Create new NEWS content
    Given I am logging in as "john"
       And I visit "john/node/add/news"
      When I fill in "Title" with "Semester Date Revised"
       And I press "Save"
       And I sleep for "2"
      Then I should see "Semester Date Revised"

  @api @features_first @news_content
  Scenario: Edit news content
    Given I am logging in as "john"
       And I visit the unaliased edit path of "news/semester-date-revised" on vsite "john"
      When I fill in "Title" with "Semester Date postponed"
       And I press "Save"
       And I sleep for "2"
      Then I should see "Semester Date postponed"

  @api @features_first @news_content
  Scenario: Delete news content
    Given I am logging in as "john"
       And I visit the unaliased edit path of "news/semester-date-revised" on vsite "john"
      When I click "Delete this news"
      Then I should see "This action cannot be undone."
       And I press "Delete"
      Then I should see "has been deleted"