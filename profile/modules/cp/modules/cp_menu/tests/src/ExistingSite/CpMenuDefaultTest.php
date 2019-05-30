<?php

namespace Drupal\Tests\cp_menu\ExistingSite;

use Drupal\Tests\openscholar\ExistingSite\OsExistingSiteTestBase;

/**
 * Class CpMenuDefaultTest.
 *
 * @group other
 * @group kernel
 *
 * @package Drupal\Tests\cp_menu\ExistingSite
 */
class CpMenuDefaultTest extends OsExistingSiteTestBase {
  /**
   * Test group.
   *
   * @var \Drupal\group\Entity\GroupInterface
   */
  protected $group;

  /**
   * Group administrator.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $groupAdmin;

  /**
   * Database service.
   *
   * @var \Drupal\Core\Database\Database
   */
  protected $database;

  /**
   * Menu Link manager service.
   *
   * @var \Drupal\Core\Menu\MenuLinkManagerInterface
   */
  protected $menuLink;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->group = $this->createGroup([
      'path' => [
        'alias' => '/test-menu',
      ],
    ]);
    $this->groupAdmin = $this->createUser();
    $this->addGroupAdmin($this->groupAdmin, $this->group);
    $this->drupalLogin($this->groupAdmin);
    $this->id = $this->group->id();

    $this->database = $this->container->get('database');
    $this->menuLink = $this->container->get('plugin.manager.menu.link');

  }

  /**
   * Tests that two menus are created by default.
   */
  public function testDefaultMenu(): void {
    $query = $this->database
      ->select('config', 'con')
      ->fields('con', [
        'name',
      ]);
    $query->condition('con.name', "system.menu.menu-primary-$this->id");
    $query->condition('con.name', "system.menu.menu-secondary-$this->id");
    $menus = $query->execute();
    $this->assertNotNull($menus, 'No matching menus found.');
  }

  /**
   * Tests that Primary menu has links and is not empty.
   */
  public function testDefaultLinks(): void {
    $menuCount = $this->menuLink->countMenuLinks("menu-primary-$this->id");
    $this->assertNotEquals('0', $menuCount);
  }

}
