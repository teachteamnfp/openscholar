Feature:
  Testing the page functionality.

  @frontend @feature_first
  Scenario: Add a page content.
    Given I am logging in as "john"
      And I visit "john/node/add/page"
      And I fill in "Title" with "Page One"
      And I fill in "Body" with "New Page for testing"
      And I press "Save"
     Then I should see "Page One"
     Then I should see "New Page for testing"

  @frontend @feature_first
  Scenario: Edit existing page content.
    Given I am logging in as "john"
      And I visit the unaliased edit path of "page-one" on vsite "john"
      And I fill in "Title" with "Page One is edited"
      And I press "Save"
     Then I should see "Page One is edited"

  @frontend @feature_first
  Scenario: Add existing subpage
    Given I am logging in as "john"
      And I add a existing sub page named "Page One is edited" under the page "About"
      And I visit "john/page-one"
     Then I should see "HOME / ABOUT /"

  @frontend @feature_first
  Scenario: Delete existing page
    Given I am logging in as "john"
      And I visit the unaliased edit path of "page-one" on vsite "john"
      And I click "Delete this page"
     Then I should see "This action cannot be undone."
      And I press "Delete"
     Then I should see "has been deleted"

  @frontend @feature_first
  Scenario: Permission to add page Content
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
      And I visit "john/node/add/page"
      And I fill in "Title" with "About Michelle"
      And I press "Save"
     Then I should see "About Michelle"

  @frontend @feature_first
  Scenario: Permission to edit own page content
    Given I am logging in as "michelle"
      And I visit the unaliased edit path of "about-michelle" on vsite "john"
      And I fill in "Title" with "About Michelle Obama"
      And I press "Save"
     Then I should see "About Michelle Obama"

  @frontend @feature_first
  Scenario: Permission to edit any page content
    Given I am logging in as "alexander"
      And I visit the unaliased edit path of "about-michelle" on vsite "john"
     Then I should see "Access Denied"

  @frontend @feature_first
  Scenario: Permission to delete any page content
    Given I am logging in as "alexander"
      And I visit the unaliased delete path of "about-michelle" on vsite "john"
     Then I should see "Access Denied"

  @frontend @feature_first
  Scenario: Permission to delete own page content
    Given I am logging in as "michelle"
      And I visit the unaliased edit path of "about-michelle" on vsite "john"
      And I click "Delete this page"
     Then I should see "This action cannot be undone."
      And I press "Delete"
     Then I should see "has been deleted"
