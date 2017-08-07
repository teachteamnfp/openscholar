Feature:
  Testing booklets

  @api @features_first @test_the_booklets_tab @os_booklets
  Scenario: Test the Booklets tab
     Given I visit "john"
      When I click "Booklets"
      Then I should see "First booklets"

  @api @features_first @test_the_booklets_archive @os_booklets
  Scenario: Test the Booklets archive
    Given I visit "john"
      And I click "Booklets"
      And I should see "Booklets posts by month"
     When I visit "john/booklets/archive/all"
      And I should see "First booklets"
      And I visit "john/booklets/archive/all/201301"
     Then I should see "January 2013"
      And I should not see "First booklets"

  @api @wip @testing_the_import_of_booklets_from_rss. @os_booklets
  Scenario: Testing the import of booklets from RSS.
    Given I am logging in as "admin"
      And I import the booklets for "john"
     When I visit "john/os-importer/booklets/manage"
      And I should see "John booklets importer"
      And I import the feed item "NASA"
     Then I should see the feed item "NASA" was imported
      And I should see "NASA stands National Aeronautics and Space Administration."

  @api @features_first @update_the_created_date_of_a_booklets_to_be_older_than_should @os_booklets
  Scenario: Update the created date of a booklets to be older than should
            be allowed.
    Given I am logging in as "john"
      And I visit "tesla/node/22/edit"
     When I fill in "Posted on" with "1901-05-30 10:43:58 -0400"
      And I press "Save"
      And I sleep for "2"
     Then I should see "Please enter a valid date for 'Posted on'"
     
 @api @features_first @update_the_created_date_of_a_booklets_to_be_futher_in_the_furture @os_booklets
  Scenario: Update the created date of a booklets to be futher in the furture
            than allowed.
    Given I am logging in as "john"
      And I visit "tesla/node/22/edit"
     When I fill in "Posted on" with "3040-05-30 10:43:58 -0400"
      And I press "Save"
      And I sleep for "2"
     Then I should see "Please enter a valid date for 'Posted on'"

 @api @features_first @create_new_booklets_content @os_booklets @create_new_booklets_content
 Scenario: Create new booklets content
    Given I am logging in as "john"
      And I visit "john/node/add/book"
     When I fill in "Title" with "Profiles In Courage"
     When I fill in "Body" with "Profiles in Courage is a 1957 Pulitzer Prize-winning volume of short biographies describing acts of bravery and integrity by eight United States Senators."
      And I press "Save"
      And I sleep for "2"
     Then I should see "Profiles In Courage"
      And I should see "Pulitzer Prize-winning volume of short biographies"

 @api @features_first @edit_existing_booklets_content @os_booklets @edit_existing_booklets_content
 Scenario: Edit existing booklets content
    Given I am logging in as "john"
      And I visit the unaliased edit path of "john/booklets/day-life-potus"
     When I fill in "Title" with "Another day in the life of The POTUS."
     When I fill in "Body" with "Each day the President eats lunch."
      And I press "Save"
      And I sleep for "2"
     Then I should see "Another day in the life of The POTUS."
      And I should see "Each day the President eats lunch."

 @api @features_first @administer_booklets_settings @os_booklets @administer_booklets_settings
 Scenario: Administer Booklets Settings
    Given I am logging in as "john"
     When I visit "john/booklets"
     When I make sure admin panel is open
     When I click "App Settings"
     When I click "Booklets Comments"
     When I sleep for "2"
     Then I should see "Choose which comment type you'd like to use"


 @api @features_first @select_private_comments @os_booklets @select_private_comments
 Scenario: Select "Private comments"
    Given I am logging in as "john"
     When I visit "john/booklets"
      And I make sure admin panel is open
      And I click "App Settings"
      And I click "Booklets Comments"
      And I select the radio button named "booklets_comments_settings" 
      And I press "Save"
     Then I should see "Add new comment"

 @api @features_first @select_no_comments @os_booklets @select_no_comments
 Scenario: Select "No Comments"
    Given I am logging in as "john"
      And I visit "john/booklets"
      And I make sure admin panel is open
      And I click "App Settings"
      And I click "Booklets Comments"
      And I click on the radio button named "booklets_comments_settings" with value "nc"
      And I press "Save"
      And I sleep for "5"
     Then I should not see "Add new comment"

 @api @features_first @delete_any_booklets_content @os_booklets @delete_booklets_content
 Scenario: Delete booklets content
    Given I am logging in as "john"
      And I visit the unaliased edit path of "john/booklets/day-life-potus"
     When I click "Delete this booklets entry"
     Then I should see "Are you sure you want to delete"
      And I click "Delete"
     Then I should see "has been deleted"
