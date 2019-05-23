<?php

namespace Drupal\Tests\cp_roles\Traits;

use Drupal\group\Entity\GroupRole;
use Drupal\group\Entity\GroupRoleInterface;

/**
 * Helper methods for CpRoles tests.
 */
trait CpRolesTestTrait {

  /**
   * Creates a new GroupRole entity.
   *
   * @param array $values
   *   Default values for the entity.
   *
   * @return \Drupal\group\Entity\GroupRoleInterface
   *   The new entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function createGroupRole(array $values = []): GroupRoleInterface {
    $group_role = GroupRole::create($values + [
      'id' => $this->randomMachineName(),
      'label' => $this->randomMachineName(),
      'group_type' => 'personal',
    ]);
    $group_role->save();

    $this->markConfigForCleanUp($group_role);

    return $group_role;
  }

}
