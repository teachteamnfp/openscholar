Feature:
  Testing the creation of the a new site.

  @javascript  @frontend
  Scenario: Test the creation of a new site and verify that we don't get JS alert.
    Given I am logging in as "admin"
      And I wait for page actions to complete
     When I visit "/"
      And I click "Create Your Site Here"
      And I click on the "Individual Scholar" control
      And I fill in "individual-scholar" with "mysite"
      And I sleep for "5"
      And I press "Next"
      And I sleep for "1"
      And I click on the "Next" control in the ".preNxt" element
      And I sleep for "1"
      And I click on the "Finish" control in the ".preNxt" element
      And I sleep for "5"
      And I press "Take me to my site"
     Then I should see "mysite" in "mysite"