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
      And I visit "john/node/add/bio"
      And I fill in "Title" with "JFK bio"
      And I fill in "Body" with "JFK was an American politician who served as the 35th President of the United States from January 1961 until his assassination in November 1963."
      And the widget "All Posts" is set in the "News" page with the following <settings>:
          | Content Type             | All    | select list |
          | Display style            | Teaser | select list |
      And I visit "john"
      And I sleep for "2"
      And I make sure admin panel is open
      And I open the admin panel to "Settings"
      And I click on the "Enable / Disable Apps" control
      And I set feature "Publications" to "Everyone" on "john"
      And I set feature "Publications" to "Site Members" on "john"
      And I press the "Close Menu" button
      And I logout
      And I visit "john/news"
      And I should not see "JFK bio"
