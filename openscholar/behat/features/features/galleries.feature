Feature:
  Testing the galleries tab.

  @api @wip
  Scenario: Test the Galleries tab
    Given I visit "john"
     When I click "Galleries"
      And I click "Kittens gallery"
     Then I should see the images:
      | slideshow1 |
      | slideshow2 |
      | slideshow3 |

  @api @debug @wip
  Scenario: Test the Galleries tab
    Given I visit "/user"
     Then I should print page


  @api @wip
  Scenario: Verfity that "galleries" tab shows all nodes.
    Given I visit "john/galleries/science/wind"
     Then I should see "Kittens gallery"
      And I should see "JFK"

  @api @wip
  Scenario: Verfity that "galleries" tab shows can filter nodes by term.
     Given I visit "john/galleries/science/fire"
      Then I should see "Kittens gallery"
       And I should not see "jfk"

  @api @features_first @create_new_image_gallery_content @image_gallery_content
  Scenario: Create new image gallery content
     Given I am logging in as "john"
        And I visit "john/node/add/media-gallery"
       When I fill in "Title" with "Safari"
       When I fill in "Description" with "Visit to world safari"
        And I press "Save"
        And I sleep for "2"
       Then I should see "Safari"
       And I should see "Visit to world safari"

  @api @features_first @edit_existing_image_gallery_content @image_gallery_content
  Scenario: Edit existing image gallery content
     Given I am logging in as "john"
        And I visit the unaliased edit path of "galleries/safari" on vsite "john"
       When I fill in "Title" with "World Safari"
       When I fill in "Description" with "Enjoying world safari"
        And I press "Save"
        And I sleep for "2"
       Then I should see "World Safari"
       And I should see "Enjoying world safari"

  @api @features_first @delete_existing_image_gallery_content @image_gallery_content
  Scenario: Delete existing image gallery content
     Given I am logging in as "john"
        And I visit the unaliased edit path of "galleries/safari" on vsite "john"
       And I sleep for "2"
      When I click "Delete this media gallery"
      Then I should see "This action cannot be undone."
       And I press "Delete"
       Then I should see "has been deleted"
  @api @javascript
  Scenario: Add media to existing gallery
     Given I am logging in as "john"
       And I visit "john/galleries/safari"
       And I sleep for "2"
      When I click "Add media"
       And I wait "1 second" for the media browser to open
       And I should wait for the text "Please wait while we get information on your files." to "disappear"
       And I drop the file "safari.jpg" onto the "Drag and drop files here." area
       And I should wait for "File Edit" directive to "appear"
       And I fill in the field "Alt Text" with the node "safari"
       And I click on the "Save" control
     Then I should see the images:
      | safari.jpg |