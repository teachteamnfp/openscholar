<?php

namespace Drupal\Tests\cp_roles\ExistingSite;

use Drupal\group\Entity\GroupRole;

/**
 * Tests CpRoles permission storage.
 *
 * @group functional
 * @group cp
 */
class CpRolesPermissionStorageTest extends CpRolesExistingSiteTestBase {

  /**
   * Group administrator.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $groupAdmin;

  /**
   * Test group role.
   *
   * @var \Drupal\group\Entity\GroupRoleInterface
   */
  protected $groupRole;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->groupAdmin = $this->createUser();
    $this->addGroupAdmin($this->groupAdmin, $this->group);
    $this->groupRole = $this->createRoleForGroup($this->group, [
      'id' => 'cprolepem',
    ]);
  }

  /**
   * Tests whether permissions are correctly saved for a custom role.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   */
  public function testSave(): void {
    $this->drupalLogin($this->groupAdmin);

    $this->visit("/{$this->group->get('path')->getValue()[0]['alias']}/cp/users/permissions");

    $this->getSession()->getPage()->find('css', "[name=\"personal-{$this->group->id()}_cprolepem[create group_node:events entity]\"]")->check();
    $this->getSession()->getPage()->find('css', "[name=\"personal-{$this->group->id()}_cprolepem[create group_node:events content]\"]")->check();
    $this->getSession()->getPage()->pressButton('Save permissions');

    $group_role = GroupRole::load($this->groupRole->id());
    /** @var array $permissions */
    $permissions = $group_role->getPermissions();
    $this->assertContains('create group_node:events entity', $permissions);
    $this->assertContains('create group_node:events content', $permissions);
  }

}
