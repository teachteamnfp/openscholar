<?php

namespace Drupal\Tests\os_classes\ExistingSite;

use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Class AddClassMaterialLinkTest.
 *
 * @group functional
 *
 * @package Drupal\Tests\os_publications\ExistingSite
 */
class AddClassMaterialLinkTest extends ExistingSiteBase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->user = $this->createUser([], '', TRUE);
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
  public function testAddMaterialLinkAsAdmin() {
    $this->drupalLogin($this->user);

    $this->drupalGet('node/' . $this->class->id());
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->linkByHrefExists('add/paragraph/class_material');
  }

  /**
   * Test Add class material link as anonymous user.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testAddMaterialLinkAsAnon() {
    $this->drupalLogin($this->simpleUser);

    $this->drupalGet('node/' . $this->class->id());
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->linkByHrefNotExists('add/paragraph/class_material');
  }

  /**
   * Test Add Class material Link on classes view as Admin user.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testAddLinkOnClassesViewAsAdmin() {
    $this->drupalLogin($this->user);

    $this->drupalGet('classes');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->linkByHrefExists('add/paragraph/class_material');
  }

  /**
   * Test Add Class material Link on classes view as anonymous user.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testAddLinkOnClassesViewAsAnon() {
    $this->drupalLogin($this->simpleUser);

    $this->drupalGet('classes');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->linkByHrefNotExists('add/paragraph/class_material');
  }

}
