Feature:
  Verify that privacy of bundles is respected in the LOP widget.

  @api @wip
  Scenario: Verify that anonymous user can see public bundles in the LOP.
    Given I am logging in as "john"
      And the widget "All Posts" is set in the "News" page with the following <settings>:
          | Content Type             | All    | select list |
          | Display style            | Teaser | select list |
      And I logout
     When I visit "john/news"
     Then I should see "John F. Kennedy: A Biography"

  @api @widgets @javascript @eam_352
  Scenario: Verify that private bundles don't show up in the LOP.
    Given I am logging in as "john"
      And I visit "john/node/add/blog"
      And I fill in "Title" with "Cuban Missle Crisis"
      And I fill in "Body" with "The Cuban Missle Crisis was a 13-day (October 16–28, 1962) confrontation between the United States and the Soviet Union concerning American ballistic missile deployment."
      And the widget "All Posts" is set in the "News" page with the following <settings>:
          | Content Type             | All    | select list |
          | Display style            | Teaser | select list |
      And I visit "john"
      And I sleep for "2"
      And I make sure admin panel is open
      And I open the admin panel to "Settings"
      And I click on the "Enable / Disable Apps" control
      And I set feature "Blog" to "Everyone" on "john"
      And I set feature "Blog" to "Site Members" on "john"
      And I press the "Close Menu" button
      And I logout
      And I visit "john/news"
      And I should not see "Cuban Missle Crisis"

# force test - Wed Aug 23 21:12:18 EDT 2017
