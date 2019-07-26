<?php

namespace Drupal\Tests\cp_users\ExistingSite;

/**
 * Tests CpUsers storage.
 *
 * @group kernel
 * @group cp
 */
class CpUsersStorageTest extends CpUsersExistingSiteTestBase {

  /**
   * Tests custom role save.
   *
   * @covers ::cp_users_entity_presave
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testSave(): void {
    $group_role = $this->createRoleForGroup($this->group, [
      'id' => 'cprole',
    ]);

    $this->assertEquals("personal-{$this->group->id()}_cprole", $group_role->id());
  }

  /**
   * Test custom role edit.
   *
   * @covers ::cp_users_entity_presave
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testEdit(): void {
    $group_role = $this->createRoleForGroup($this->group, [
      'id' => 'role_edit',
      'label' => 'Role',
    ]);

    $group_role->set('label', 'Role Edited')->save();

    $this->assertEquals("personal-{$this->group->id()}_role_edit", $group_role->id());
    $this->assertEquals('Role Edited', $group_role->label());
  }

}
