Feature:
  Testing JS for the page edit.

  @javascript @frontend
  Scenario: Verify the page path is not changed after editing.
    Given I am logging in as "admin"
      And I visit "john/node/add/page"
      And I fill in "Title" with "Testing page"
      And I press "Save"
      And I edit the page "Testing page"
      And I save the page address
     When I fill in "Title" with "Other page"
     Then I verify the page kept the same
      And I press "Save"
      And I verify the url did not changed

  @frontend
  Scenario: Add a page content.
    Given I am logging in as "john"
      And I visit "john/node/add/page"
      And I fill in "Title" with "Page One"
      And I fill in "Body" with "New Page for testing"
      And I press "Save"
     Then I should see "Page"
     Then I should see "New Page for testing"

  @frontend
  Scenario: Edit existing page content.
    Given I am logging in as "john"
      And I visit the unaliased edit path of "page-one" on vsite "john"
      And I fill in "Title" with "Page one is edited"
      And I press "Save"
     Then I should see "Page one is edited"

  @frontend
  Scenario: Add existing subpage
    Given I am logging in as "john"
      And I add a existing sub page named "Page one is edited" under the page "About"
      And I visit "john/page-one"
     Then I should see "HOME / ABOUT /"

  @frontend
  Scenario: Delete existing subpage
    Given I am logging in as "john"
      And I visit the unaliased edit path of "page-one" on vsite "john"
      And I click "Delete this page"
     Then I should see "This action cannot be undone."
      And I press "Delete"
     Then I should see "has been deleted"
