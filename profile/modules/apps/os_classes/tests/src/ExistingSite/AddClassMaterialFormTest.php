<?php

namespace Drupal\Tests\os_classes\ExistingSite;

use Drupal\Tests\openscholar\ExistingSite\OsExistingSiteTestBase;

/**
 * Class AddClassMaterialFormTest.
 *
 * @group functional
 * @group classes
 *
 * @package Drupal\Tests\os_publications\ExistingSite
 */
class AddClassMaterialFormTest extends OsExistingSiteTestBase {

  /**
   * Group member.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $groupMember;

  /**
   * Test class.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $class;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->groupMember = $this->createUser();
    $this->addGroupAdmin($this->groupMember, $this->group);

    $this->container->get('vsite.context_manager')->activateVsite($this->group);
    $this->class = $this->createNode([
      'type' => 'class',
      'title' => $this->randomString(),
      'field_semester' => '2019',
      'field_class_materials[0][subform][field_title][0][value]' => $this->randomString(),
      'field_class_materials[0][subform][field_body][0][value]' => $this->randomString(),
    ]);
    $this->group->addContent($this->class, 'group_node:class');
  }

  /**
   * Test Add Class material link as admin.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testClassMaterialPage() {
    $this->drupalLogin($this->groupMember);

    $this->visitViaVsite("node/{$this->class->id()}", $this->group);
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
    $this->drupalGet($current_url);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('This is a random test class material');
  }

}
