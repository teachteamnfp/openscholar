<?php

namespace Drupal\Tests\cp_roles\Traits;

use Drupal\group\Entity\GroupInterface;
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

  /**
   * Creates a new GroupRole for a group.
   *
   * @param \Drupal\group\Entity\GroupInterface $group
   *   The group.
   * @param array $values
   *   Default values for the entity.
   *
   * @return \Drupal\group\Entity\GroupRoleInterface
   *   The new entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function createRoleForGroup(GroupInterface $group, array $values = []): GroupRoleInterface {
    /** @var \Drupal\vsite\Plugin\VsiteContextManagerInterface $vsite_context_manager */
    $vsite_context_manager = $this->container->get('vsite.context_manager');
    $vsite_context_manager->activateVsite($group);

    return $this->createGroupRole($values);
  }

}
