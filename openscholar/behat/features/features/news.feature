Feature:
  Testing the news tab.

  @api @features_second
  Scenario: Test the News tab
    Given I visit "john"
     When I click "News"
      And I click "I opened a new personal"
     Then I should see "This is a new site generated via the vsite options in open scholar."

  @api @features_first
  Scenario: Create new NEWS content
    Given I am logging in as "john"
      And I visit "john/node/add/news"
     When I fill in "Title" with "Semester Date Revised"
      And I press "Save"
      And I sleep for "2"
     Then I should see "Semester Date Revised"

  @api @features_first
  Scenario: Edit existing news content
    Given I am logging in as "john"
      And I visit the unaliased edit path of "news/semester-date-revised" on vsite "john"
     When I fill in "Title" with "Semester Date postponed"
      And I press "Save"
      And I sleep for "2"
     Then I should see "Semester Date postponed"

  @api @features_first
  Scenario: Delete existing news content
    Given I am logging in as "john"
      And I visit the unaliased edit path of "news/semester-date-revised" on vsite "john"
     When I click "Delete this news"
     Then I should see "This action cannot be undone."
      And I press "Delete"
     Then I should see "has been deleted"

  @api @features_second
  Scenario: Permission to create news content
    Given I am logging in as "john"
      And I visit "john/cp/users/add"
      And I fill in "Member" with "alexander"
      And I press "Add member"
      And I sleep for "5"
     Then I should see "alexander has been added to the group John."
      And I visit "john/cp/users/add"
      And I fill in "Member" with "michelle"
      And I press "Add member"
      And I sleep for "5"
     Then I should see "michelle has been added to the group John."
      And I visit "user/logout"
    Given I am logging in as "michelle"
      And I visit "john/node/add/news"
      And I fill in "Title" with "Semester Notification"
      And I press "Save"
      And I sleep for "2"
     Then I should see "Semester Notification"

  @api @features_second
  Scenario: Permission to edit own news content
    Given I am logging in as "michelle"
      And I visit the unaliased edit path of "news/semester-notification" on vsite "john"
      And I fill in "Title" with "Semester dates published"
      And I press "Save"
     Then I should see "Semester dates published"

  @api @features_second
  Scenario: Permission to edit any news content
    Given I am logging in as "alexander"
      And I visit the unaliased edit path of "news/semester-notification" on vsite "john"
     Then I should see "Access Denied"

  @api @features_second
  Scenario: Permission to delete any news content
    Given I am logging in as "alexander"
      And I visit the unaliased delete path of "news/semester-notification" on vsite "john"
     Then I should see "Access Denied"

  @api @features_second
  Scenario: Permission to delete own news content
    Given I am logging in as "michelle"
      And I visit the unaliased edit path of "news/semester-notification" on vsite "john"
      And I click "Delete this news"
     Then I should see "This action cannot be undone."
      And I press "Delete"
     Then I should see "has been deleted"
