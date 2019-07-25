<?php

namespace Drupal\cp_roles;

use Drupal\group\Entity\GroupInterface;
use Drupal\group\Entity\GroupRoleInterface;

/**
 * Provides an interface for editable cp_roles.
 */
interface CpRolesEditableInterface {

  /**
   * Get non-configurable roles for a group.
   *
   * @param \Drupal\group\Entity\GroupInterface $group
   *   The group.
   *
   * @return string[]
   *   The roles.
   */
  public function getNonConfigurableGroupRoles(GroupInterface $group): array;

  /**
   * Get default roles for a group.
   *
   * @param \Drupal\group\Entity\GroupInterface $group
   *   The group.
   *
   * @return string[]
   *   The roles.
   */
  public function getDefaultGroupRoles(GroupInterface $group): array;

  /**
   * Checks if the group role is a default role.
   *
   * @param \Drupal\group\Entity\GroupRoleInterface $group_role
   *   The group role to check.
   *
   * @return bool
   *   TRUE if it is a default one, otherwise FALSE.
   */
  public function isDefaultGroupRole(GroupRoleInterface $group_role): bool;

}
