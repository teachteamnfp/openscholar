Feature:
  Testing the classes tab.
  As a user visiting different content-type tabs
  I should be able to filter by terms
  And see nodes of the content-type that are also attached to the selected term.

  @api @features_first
  Scenario: Test the Classes tab
    Given I visit "john"
      And I click "Classes"
      And I click "John F. Kennedy"
     When I should see the link "Wikipedia page on JFK"
     Then I should see the link "Who was JFK?"

  @api @features_first @create_new_class_content @os_classes
  Scenario: Create new class content
     Given I am logging in as "john"
       And I visit "john/node/add/class"
      When I fill in "Title" with "Political Science 101"
      When I fill in "Body" with "The great Greek philosopher Aristotle once called political science the master science. In this lesson, you'll learn what political science is, different subfields in the discipline, and why the study of political science is important."
       And I press "Save"
       And I sleep for "2"
      Then I should see "Political Science 101"
       And I should see "The great Greek philosopher Aristotle once called political"
       And I should see "Semester"
       And I should see "Offered"

  @api @features_first @edit_existing_class_content @os_classes
  Scenario: Edit existing class content
     Given I am logging in as "john"
       And I visit the unaliased edit path of "classes/political-science-101" on vsite "john"
      When I fill in "Title" with "Political Science 102"
      When I fill in "Body" with "As an introductory course, POLSC102 will focus on the basic principles of political science by combining historical study of the discipline's greatest thinkers with analysis of contemporary issues."
       And I press "Save"
       And I sleep for "2"
      Then I should see "Political Science 102"
       And I should see "As an introductory course, POLSC102 will focus on the basic principles of political science"
       And I should see "Semester"
       And I should see "Offered"

  @api @features_first @create_new_class_material_content @os_classes
  Scenario: Create new class content
     Given I am logging in as "john"
       # Note the modified title does not change the URL
       And I visit "classes/political-science-101"
       And I click "Add class material"
       And I fill in "Title" with "Overview"
       And I fill in "Body" with "Political Theory is chiefly concerned with how best to arrange our collective lives, with particular attention to the necessity for and rights and obligations of ‘rule,’ as well as the limits of that important power."
       And I press "Save"
       And I sleep for "2"
      Then I should see "Overview"
       And I should see "Class:"
       And I should see "Political Science"
       And I should see breadcrumbs "HOME / CLASSES / POLITICAL SCIENCE 101 / CLASS MATERIAL"


  @api @features_first @edit_existing_class_material_content @os_classes
  Scenario: Edit existing class material content
     Given I am logging in as "john"
       And I visit the unaliased edit path of "classes/political-science-101" on vsite "john"
      When I fill in "Title" with "Political Science 102"
      When I fill in "Body" with "As an introductory course, POLSC102 will focus on the basic principles of political science by combining historical study of the discipline's greatest thinkers with analysis of contemporary issues."
       And I press "Save"
       And I sleep for "2"
      Then I should see "Political Science 102"
       And I should see "As an introductory course, POLSC102 will focus on the basic principles of political science"
       And I should see "Semester"
       And I should see "Offered"

  @api @features_first @delete_any_class_content @os_classes_1247
  Scenario: Delete any class content (permissions)
     Given I am logging in as "michelle"
       And I visit the unaliased path of "classes/political-science-101" on vsite "john" and append "delete"
      Then I should see "Access denied"

  @api @features_first @create_new_class_material_content @os_classes_1247
  Scenario: Create new class material content (permissions)
     Given I am logging in as "michelle"
       And I visit "john/node/add/class"
      Then I should not see "Create Class"
#      And I should see "Access denied"

  @api @features_first @edit_any_class_material_content @os_classes_1247
  Scenario: Edit any class material content (permissions)
     Given I am logging in as "michelle"
       And I visit the unaliased path of "classes/political-science-101/materials/overview" on vsite "john" and append "edit"
      Then I should see "Access denied"

  @api @features_first @delete_any_class_material_content @os_classes_1247
  Scenario: Delete any class material content (permissions)
     Given I am logging in as "michelle"
       And I visit the unaliased path of "classes/political-science-101/materials/overview" on vsite "john" and append "delete"
      Then I should see "Access denied"

  # Deletion scenarios must be last so we have classes and materials to test!
  @api @features_first @delete_class_material_content @os_classes
  Scenario: Delete class material content
     Given I am logging in as "john"
       And I visit the unaliased path of "classes/political-science-101/materials/overview" on vsite "john" and append "delete"
       And I sleep for "2"
      When I click "Delete this class"
      Then I should see "Are you sure you want to delete"
       And I press "Delete"
      Then I should see "has been deleted"

  @api @features_first @delete_class_content @os_classes
  Scenario: Delete class content
     Given I am logging in as "john"
       And I visit the unaliased edit path of "classes/political-science-101" on vsite "john"
       And I sleep for "2"
      When I click "Delete this class"
      Then I should see "Are you sure you want to delete"
       And I press "Delete"
      Then I should see "has been deleted"
