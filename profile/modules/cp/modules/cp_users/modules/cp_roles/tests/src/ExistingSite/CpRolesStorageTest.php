<?php

namespace Drupal\Tests\cp_roles\ExistingSite;

/**
 * Tests CpRoles storage.
 *
 * @group kernel
 * @group cp
 */
class CpRolesStorageTest extends CpRolesExistingSiteTestBase {

  /**
   * Tests custom role save.
   *
   * @covers ::cp_roles_entity_presave
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testSave(): void {
    $group_role = $this->createRoleForGroup($this->group, [
      'id' => 'cprole',
    ]);

    $this->assertEquals("personal-{$this->group->id()}-cprole", $group_role->id());
  }

}
