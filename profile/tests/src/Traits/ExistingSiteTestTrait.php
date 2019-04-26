<?php

namespace Drupal\Tests\openscholar\Traits;

use Drupal\group\Entity\GroupInterface;
use Drupal\user\UserInterface;

/**
 * Provides a trait for openscholar tests.
 */
trait ExistingSiteTestTrait {

  /**
   * Creates a group.
   *
   * @param array $values
   *   (optional) The values used to create the entity.
   *
   * @return \Drupal\group\Entity\GroupInterface
   *   The created group entity.
   */
  protected function createGroup(array $values = []): GroupInterface {
    $storage = $this->container->get('entity_type.manager')->getStorage('group');
    $group = $storage->create($values + [
      'type' => 'personal',
      'label' => $this->randomMachineName(),
    ]);
    $group->enforceIsNew();
    $group->save();

    $this->markEntityForCleanup($group);

    return $group;
  }

  /**
   * Creates a user and tracks it for automatic cleanup.
   *
   * @param array $permissions
   *   Array of permission names to assign to user. Note that the user always
   *   has the default permissions derived from the "authenticated users" role.
   * @param string $name
   *   The user name.
   *
   * @return \Drupal\user\Entity\User|false
   *   A fully loaded user object with pass_raw property, or FALSE if account
   *   creation fails.
   */
  protected function createAdminUser(array $permissions = [], $name = NULL) {
    return $this->createUser($permissions, $name, TRUE);
  }

  /**
   * Adds a user to group as admin.
   *
   * @param \Drupal\user\UserInterface $admin
   *   The user.
   * @param \Drupal\group\Entity\GroupInterface $group
   *   The user.
   */
  protected function addGroupAdmin(UserInterface $admin, GroupInterface $group): void {
    $group->addMember($admin, [
      'group_roles' => 'personal-administrator',
    ]);
  }

}
