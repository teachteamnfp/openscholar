Feature:
  Testing the blog tab.

  @api @features_first
  Scenario: Test the Blog tab
     Given I visit "john"
      When I click "Blog"
      Then I should see "First blog"

  @api @features_first
  Scenario: Test the Blog archive
    Given I visit "john"
      And I click "Blog"
      And I should see "Blog posts by month"
     When I visit "john/blog/archive/all"
      And I should see "First blog"
      And I visit "john/blog/archive/all/201301"
     Then I should see "January 2013"
      And I should not see "First blog"

  @api @wip
  Scenario: Testing the import of blog from RSS.
    Given I am logging in as "admin"
      And I import the blog for "john"
     When I visit "john/os-importer/blog/manage"
      And I should see "John blog importer"
      And I import the feed item "NASA"
     Then I should see the feed item "NASA" was imported
      And I should see "NASA stands National Aeronautics and Space Administration."

  @api @features_first
  Scenario: Update the created date of a blog to be older than should
            be allowed.
    Given I am logging in as "john"
      And I visit "tesla/node/22/edit"
     When I fill in "Posted on" with "1901-05-30 10:43:58 -0400"
      And I press "Save"
      And I sleep for "2"
     Then I should see "Please enter a valid date for 'Posted on'"
     
 @api @features_first
  Scenario: Update the created date of a blog to be futher in the furture
            than allowed.
    Given I am logging in as "john"
      And I visit "tesla/node/22/edit"
     When I fill in "Posted on" with "3040-05-30 10:43:58 -0400"
      And I press "Save"
      And I sleep for "2"
     Then I should see "Please enter a valid date for 'Posted on'"

 @api @features_first @create_new_blog_content @os_blog
 Scenario: Create new blog content
    Given I am logging in as "john"
      And I visit "john/node/add/blog"
     When I fill in "Title" with "A day in the life of The POTUS."
     When I fill in "Body" with "Each day the President is briefed."
      And I press "Save"
      And I sleep for "2"
     Then I should see "A day in the life of The POTUS"
      And I should see "Each day the President is briefed."

 @api @features_first @edit_existing_blog_content @os_blog
 Scenario: Edit existing blog content
    Given I am logging in as "john"
      And I visit "john/blog/day-life-potus"
      And I click on the element with css selector "link-count-node-edit first"
     When I fill in "Title" with "Another day in the life of The POTUS."
     When I fill in "Body" with "Each day the President eats lunch."
      And I press "Save"
      And I sleep for "2"
     Then I should see "Another day in the life of The POTUS."
      And I should see "Each day the President eats lunch."

 @api @features_first @administer_blog_settings @os_blog
 Scenario: Administer Blog Settings
    Given I am logging in as "john"
     When I visit "john/blog"
     When I click on the element with css selector "span#blog_comments"
     When I sleep for "2"
     Then I should see "Choose which comment type you'd like to use"

 @api @features_first @select_private_comments @os_blog
 Scenario: Select "Private comments"
    Given I am logging in as "john"
      And I visit "john/blog"
     When I click on the element with css selector "span#blog_comments"
     When I click on the element with css selector "input#edit-blog-comments-settings-comment"
     When I press "Save"
     When I sleep for "10"
     Then I should see "Add new comment"

 @api @features_first @select_no_comments @os_blog
 Scenario: Select "No Comments"
    Given I am logging in as "john"
      And I visit "john/blog"
     When I click on the element with css selector "span#blog_comments"
     When I click on the element with css selector "input#edit-blog-comments-settings-nc"
     When I press "Save"
     When I sleep for "10"
     Then I should not see "Add new comment"

 @api @features_first @delete_any_blog_content @os_blog
 Scenario: Delete blog content
    Given I am logging in as "john"
     When I visit "john/blog/day-life-potus"
     When I click on the element with css selector "li.link-count-node-delete a[href*='/delete']"
     When I sleep for "4"
     When I press "Delete"
     When I sleep for "2"
     Then I should see "Blog entry A day in the life of The POTUS has been deleted."
