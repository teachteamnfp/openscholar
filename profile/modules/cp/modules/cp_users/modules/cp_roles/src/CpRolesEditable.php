<?php

namespace Drupal\cp_roles;

use Drupal\group\Entity\GroupInterface;
use Drupal\group\Entity\GroupRoleInterface;

/**
 * Specifies the roles which cannot be edited/deleted by group admins.
 */
final class CpRolesEditable implements CpRolesEditableInterface {

  public const NON_CONFIGURABLE = [
    'anonymous',
    'outsider',
  ];

  public const NON_EDITABLE = [
    'administrator',
    'member',
    'content_editor',
  ];

  /**
   * {@inheritdoc}
   */
  public function getNonConfigurableGroupRoles(GroupInterface $group): array {
    return array_map(static function ($item) use ($group) {
      return "{$group->getGroupType()->id()}-$item";
    }, self::NON_CONFIGURABLE);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultGroupRoles(GroupInterface $group): array {
    return array_map(static function ($item) use ($group) {
      return "{$group->getGroupType()->id()}-$item";
    }, self::NON_EDITABLE);
  }

  /**
   * {@inheritdoc}
   */
  public function isDefaultGroupRole(GroupRoleInterface $group_role): bool {
    $group_type_id = $group_role->getGroupTypeId();

    $group_type_roles = array_map(static function ($item) use ($group_type_id) {
      return "$group_type_id-$item";
    }, self::NON_EDITABLE);

    return \in_array($group_role->id(), $group_type_roles, TRUE);
  }

}
