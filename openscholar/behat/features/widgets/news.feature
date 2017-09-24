Feature:
  Testing news widget.

  @api @widgets @javascript
  Scenario: Verify "Latest News" widget
    Given I am logging in as "john"
      And I visit "obama/node/add/news"
     When I fill in "Title" with "Semester Date"
      And I press "Save"
      And I sleep for "2"
     Then I should see "Semester Date"
      And I visit "obama/os/widget/boxes/os_news_latest/edit/cp-layout"
      And I sleep for "2"
      And I make sure admin panel is closed
      And I press "Save"
      And I visit "john"
      And I click the big gear
      And I click "Layout"
      And I drag the "Latest News" widget to the "sidebar-first" region
      And I visit "john"
     Then I should match the regex "latest\s+news\s+semester\s+date"
