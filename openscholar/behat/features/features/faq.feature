Feature:
  Testing the faq app.

  @api @features_first
  Scenario: Testing the migration of FAQ
    Given I am logging in as "john"
      And I visit "john/faq"
      And I should see "What does JFK stands for?"
     When I click "What does JFK stands for?"
     Then I should see "JFK stands for: John Fitzgerald Kennedy."

  @api @features_first
  Scenario: Testing the migration of FAQ
    Given I am logging in as "john"
      And I visit "john/node/add/faq"
      And I fill "edit-title" with random text
      And I press "Save"
     Then I should see the random string

  @api @features_first
  Scenario: Verify that the FAQ's body in the LOP is collapsed by default.
    Given I am logging in as "john"
      And the widget "List of posts" is set in the "FAQ" page with the following <settings>:
          | Content Type               | FAQ         | select list |
          | Display style              | Teaser      | select list |
     When I visit "john/faq"
     Then I should see that the "faq" in the "LOP" are collapsed


  @api @features_first
  Scenario: Verify that body length limits are respected
    Given I am logging in as "john"
      And I set the variable "os_wysiwyg_maximum_length_body" to "50"
      And I visit "john/node/add/faq"
     When I fill in "edit-title" with "Gonna fail"
      And I fill in "edit-body-und-0-value" with "01234567890123456789012345678901234567890123456789AAAAAA"
      And I press "Save"
     Then I should see "Answer cannot be longer than 50 characters but is currently 56 characters long."
      And I delete the variable "os_wysiwyg_maximum_length_body"
  
  @api @features_first @faq_content
  Scenario: Create new faq content
     Given I am logging in as "john"
        And I visit "john/node/add/faq"
       When I fill in "Question" with "Frequently Asked"
       When I fill in "Answer" with "Answer Cleared"
        And I press "Save"
        And I sleep for "2"
       Then I should see "Frequently Asked"
       And I should see "Answer Cleared"

  @api @features_first @faq_content
  Scenario: Delete faq content
     Given I am logging in as "john"
       And I visit the unaliased edit path of "faq/frequently-asked" on vsite "john"
       And I sleep for "2"
      When I click "Delete this faq"
      Then I should see "This action cannot be undone."
       And I press "Delete"
      Then I should see "has been deleted"

  @api @frontend_feature
  Scenario: Permission to edit FAQ
     Given I am logging in as "klark"
       And I visit the unaliased edit path of "faq/what-does-jfk-stands" on vsite "john"
      Then I should see "Access Denied"

  @api @javascript
  Scenario: Administer FAQ App Setting
     Given I am logging in as "john"
      When I visit "john"
       And I make sure admin panel is open
      When I open the admin panel to "Settings"
       And I open the admin panel to "App Settings"
       And I open the admin panel to "FAQs"
      When I sleep for "2"
      Then I should see "Choose how FAQs will display"
