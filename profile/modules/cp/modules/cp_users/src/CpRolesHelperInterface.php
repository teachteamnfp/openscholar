<?php

namespace Drupal\cp_users;

use Drupal\group\Entity\GroupInterface;
use Drupal\group\Entity\GroupRoleInterface;
use Drupal\group\Entity\GroupTypeInterface;

/**
 * Provides an interface for editable cp_roles.
 */
interface CpRolesHelperInterface {

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

  /**
   * Returns the restricted permissions for a group type.
   *
   * The permissions are supposed to be restricted only in vsite permissions
   * interface, not in the default permissions interface.
   *
   * @param \Drupal\group\Entity\GroupTypeInterface $group_type
   *   The group type.
   *
   * @return array
   *   The restricted permissions.
   *
   * @see \Drupal\cp_users\Form\CpUsersPermissionsTypeSpecificForm::buildForm()
   */
  public function getRestrictedPermissions(GroupTypeInterface $group_type): array;

}
