<?php

namespace Drupal\cp_users;

use Drupal\group\Entity\GroupInterface;

/**
 * Specifies the roles which cannot be edited/deleted by group admins.
 */
final class CpUsersEditable implements CpUsersEditableInterface {

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
  public function getNonEditableGroupRoles(GroupInterface $group): array {
    return array_map(static function ($item) use ($group) {
      return "{$group->getGroupType()->id()}-$item";
    }, self::NON_EDITABLE);
  }

}
