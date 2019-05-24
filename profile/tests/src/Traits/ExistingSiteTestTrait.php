<?php

namespace Drupal\Tests\openscholar\Traits;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\group\Entity\GroupInterface;
use Drupal\user\UserInterface;

/**
 * Provides a trait for openscholar tests.
 */
trait ExistingSiteTestTrait {

  /**
   * Configurations to clean up.
   *
   * @var \Drupal\Core\Config\Entity\ConfigEntityInterface[]
   */
  protected $cleanUpConfigs = [];

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
      'path' => [
        'alias' => "/{$this->randomMachineName()}",
      ],
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
      'group_roles' => [
        'personal-administrator',
      ],
    ]);
  }

  /**
   * Mark an config for deletion.
   *
   * Any configurations you create when running against an installed site should
   * be flagged for deletion to ensure isolation between tests.
   *
   * @param \Drupal\Core\Config\Entity\ConfigEntityInterface $config_entity
   *   The configuration to delete.
   */
  protected function markConfigForCleanUp(ConfigEntityInterface $config_entity): void {
    $this->cleanUpConfigs[] = $config_entity;
  }

}
