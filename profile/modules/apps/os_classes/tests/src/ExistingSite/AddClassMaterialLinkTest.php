<?php

namespace Drupal\Tests\os_classes\ExistingSite;

use Drupal\Tests\openscholar\ExistingSite\OsExistingSiteTestBase;

/**
 * Class AddClassMaterialLinkTest.
 *
 * @group functional
 *
 * @package Drupal\Tests\os_publications\ExistingSite
 */
class AddClassMaterialLinkTest extends OsExistingSiteTestBase {

  /**
   * Administrator and group administrator.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * Outsider.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $simpleUser;

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

    $this->adminUser = $this->createUser();
    $this->simpleUser = $this->createUser();

    $this->class = $this->createNode([
      'type' => 'class',
      'title' => $this->randomString(),
      'field_semester' => '2019',
      'field_class_materials[0][subform][field_title][0][value]' => $this->randomString(),
      'field_class_materials[0][subform][field_body][0][value]' => $this->randomString(),
    ]);
    $this->addGroupAdmin($this->adminUser, $this->group);
    $this->group->addContent($this->class, 'group_node:class');
  }

  /**
   * Test Add Class material link as admin.
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testAddMaterialLinkAsAdmin(): void {
    $this->drupalLogin($this->adminUser);

    $this->drupalGet("{$this->group->get('path')->first()->getValue()['alias']}/node/{$this->class->id()}");
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->linkByHrefExists('add/paragraph/class_material');
  }

  /**
   * Test Add class material link as a non-member.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function testAddMaterialLinkAsOutsider(): void {
    $this->drupalLogin($this->simpleUser);

    $this->drupalGet("{$this->group->get('path')->first()->getValue()['alias']}/node/{$this->class->id()}");
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->linkByHrefNotExists('add/paragraph/class_material');
  }

  /**
   * Test Add Class material Link on classes view as Admin user.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function testAddLinkOnClassesViewAsAdmin(): void {
    $this->drupalLogin($this->adminUser);

    $this->visit("{$this->group->get('path')->first()->getValue()['alias']}/classes");
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->linkByHrefExists("{$this->group->get('path')->first()->getValue()['alias']}/node/{$this->class->id()}/add/paragraph/class_material");
  }

  /**
   * Test Add Class material Link on classes view as a non-member.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function testAddLinkOnClassesViewAsOutsider(): void {
    $this->drupalLogin($this->simpleUser);

    $this->visit("{$this->group->get('path')->first()->getValue()['alias']}/classes");
    $this->assertSession()->statusCodeEquals(403);
    $this->assertSession()->linkByHrefNotExists("{$this->group->get('path')->first()->getValue()['alias']}/node/{$this->class->id()}/add/paragraph/class_material");
  }

}
