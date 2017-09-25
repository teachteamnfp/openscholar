Feature:
  Testing news widget.

  @api @widgets
  Scenario: Verify "Latest News" widget
    Given I am logging in as "john"
      And I visit "john/node/add/news"
     When I fill in "Title" with "Semester Date"
      And I press "Save"
      And I sleep for "2"
     Then I should see "Semester Date"
      And the widget "Latest News" is placed in the "News" layout
      And I visit "john/news"
     Then I should see "LATEST NEWS"