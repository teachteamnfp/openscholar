<?php

namespace Drupal\Tests\os_classes\ExistingSite;

use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Class AddClassMaterialFormTest.
 *
 * @group functional
 * @group classes
 *
 * @package Drupal\Tests\os_publications\ExistingSite
 */
class AddClassMaterialFormTest extends ExistingSiteBase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->adminUser = $this->createUser([], '', TRUE);
    $this->simpleUser = $this->createUser();

    $this->class = $this->createNode([
      'type' => 'class',
      'title' => $this->randomString(),
      'field_semester' => '2019',
      'field_class_materials[0][subform][field_title][0][value]' => $this->randomString(),
      'field_class_materials[0][subform][field_body][0][value]' => $this->randomString(),
    ]);
  }

  /**
   * Test Add Class material link as admin.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testClassMaterialPage() {
    $this->drupalLogin($this->adminUser);

    $this->drupalGet('node/' . $this->class->id());
    $this->assertSession()->statusCodeEquals(200);
    $this->clickLink('Add class material');
    $this->assertSession()->statusCodeEquals(200);
    $edit = [
      'field_title[0][value]' => 'This is a random test class material',
      'field_body[0][value]' => $this->randomString(),
    ];
    $this->submitForm($edit, 'edit-submit');

    // Test the newly created paragraph link.
    $this->assertSession()->pageTextContains('This is a random test class material');

    // Test the newly created paragraph.
    $this->clickLink('This is a random test class material');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('This is a random test class material');
    $current_url = $this->getUrl();

    // Test the newly created paragraph as Anonymous user.
    $this->drupalLogin($this->simpleUser);
    $this->drupalGet($current_url);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('This is a random test class material');
  }

}
