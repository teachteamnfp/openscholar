Feature:
  Testing booklets

 @api @features_first @create_new_booklets_content @os_booklets
 Scenario: Create new booklets content
    Given I am logging in as "john"
      And I visit "john/node/add/book"
     When I fill in "Title" with "Profiles In Courage"
     When I fill in "Body" with "Profiles in Courage is a 1957 Pulitzer Prize-winning volume of short biographies describing acts of bravery and integrity by eight United States Senators."
      And I press "Save"
      And I sleep for "5"
     Then I should see "Profiles In Courage"
      And I should see "Pulitzer Prize-winning volume of short biographies"

 @api @features_first @edit_existing_booklets_content @os_booklets
 Scenario: Edit existing booklets content
    Given I am logging in as "john"
      And I visit the unaliased edit path of "book/profiles-courage" on vsite "john"
      And I fill in "Title" with "Profiles In Courage by John F. Kennedy and Ted Sorensen"
      And I fill in "Body" with " Profiles In Courage profiles senators who defied the opinions of their party and constituents to do what they felt was right and suffered severe criticism and losses in popularity because of their actions."
      And I press "Save"
      And I sleep for "5"
     Then I should see "profiles senators who defied the opinions"
      And I should see "by John F. Kennedy and Ted Sorensen"

 @api @features_first @os_booklets @add_child_page_to_existing_booklet_content
 Scenario: Add child page to existing booklet content
    Given I am logging in as "john"
      And I visit the site "john/book/profiles-courage"
      And I click "Add child page"
      And I fill in "Title" with "John Quincy Adams"
      And I fill in "Body" with "John Quincy Adams, from Massachusetts, for breaking away from the Federalist Party."
      And I press "Save"
      And I sleep for "5"
     Then I should see "John Quincy Adams"
      And I should see "from Massachusetts, for breaking"

 @api @features_first @os_booklets @add_a_second_child_page_to_existing_booklet_content
 Scenario: Add a second child page to existing booklet content
    Given I am logging in as "john"
      And I visit the site "john/book/profiles-courage"
      And I click "Add child page"
      And I fill in "Title" with "Daniel Webster"
      And I fill in "Body" with "Daniel Webster, also from Massachusetts, for speaking in favor of the Compromise of 1850."
      And I press "Save"
      And I sleep for "5"
     Then I should see "Daniel Webster"
      And I should see "for speaking in favor of the Compromise of 1850"

 @api @features_first @delete_any_booklets_content @os_booklets
 Scenario: Delete booklets content
    Given I am logging in as "john"
      And I visit the unaliased edit path of "book/profiles-courage" on vsite "john"
     When I click "Delete this book page"
     Then I should see "Are you sure you want to delete"
     When I sleep for "5"
      And I press "Delete"
     Then I should see "has been deleted"

#@api @wip @os_booklets @change_order_of_booklet_content_in_outline
#Scenario: change order of booklet content in outline
#   Given I am logging in as "john"
#     And I visit the unaliased path of "book/profiles-courage" on vsite "john" and append "outline"
#     And I swap the order of the first and last items in the book outline
#         """
#         table[book-admin-1032934][title]=John Quincy Adams
#         table[book-admin-1032934][weight]=-15
#         table[book-admin-1032934][plid]=170518
#         table[book-admin-1032934][mlid]=170519
#         table[book-admin-1032936][title]=Daniel Webster
#         table[book-admin-1032936][weight]=-14
#         table[book-admin-1032936][plid]=170518
#         table[book-admin-1032936][mlid]=170521
#         tree_hash=uZ7q3pOkNXKHkMJb0edzsAohcb6Xjypzdg8DIB-TjZU
#         op=Save Booklet Outline
#         form_build_id=form-AKd9R2JR3_eKlDc9Dvz3yS6yukoeDLaD3oR-K-DHR1U
#         form_token=di3f5Bqn1KinfyGvopSjJghNLR7hR9wQ_vX9fQUUhZA
#         form_id=book_admin_edit
#         """
#    Then I should see
#         """
#         | TITLE             |
#         | John Quincy Adams |
#         | Daniel Webster    |
#         """

#@api @features_first @os_booklets @delete_booklet_content_in_outline
#Scenario: delete booklet content in outline
#   Given I am logging in as "john"
#     And I visit the site "john/book/profiles-courage"

#@api @features_first @os_booklets @change_order_of_booklet_content_using_"booklet_information"_field
#Scenario: change order of booklet content using "Booklet information" field
#   Given I am logging in as "john"
#     And I visit the site "john/book/profiles-courage"

#@api @features_first @os_booklets @correct_re_arrangement_of_booklet_outline_when_parent_is_deleted
#Scenario: correct re-arrangement of booklet outline when parent is deleted
#   Given I am logging in as "john"
#     And I visit the site "john/book/profiles-courage"
