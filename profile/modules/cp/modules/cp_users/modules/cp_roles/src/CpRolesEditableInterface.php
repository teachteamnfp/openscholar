<?php

namespace Drupal\cp_roles;

use Drupal\group\Entity\GroupInterface;

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
   * Get non-editable roles for a group.
   *
   * @param \Drupal\group\Entity\GroupInterface $group
   *   The group.
   *
   * @return string[]
   *   The roles.
   */
  public function getNonEditableGroupRoles(GroupInterface $group): array;

}
