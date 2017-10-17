Feature:
  Testing news widget.

  @api @widgets
  Scenario: Verify "Latest News" widget
    Given I am logging in as "john"
      And the widget "Latest News" is set in the "News" page with the following <settings>:
          | Content Type               | News                 | select list |
          | Display style              | Title                | select list |
          | Sorted By                  | Newest post          | select list |
      And I visit "john/news"
     Then I should see "LATEST NEWS"