<?php

namespace Drupal\cp_users;

use Drupal\group\Entity\GroupInterface;
use Drupal\group\Entity\GroupRoleInterface;
use Drupal\group\Entity\GroupTypeInterface;

/**
 * Specifies the roles which cannot be edited/deleted by group admins.
 */
final class CpRolesHelper implements CpRolesHelperInterface {

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

  /**
   * {@inheritdoc}
   */
  public function getRestrictedPermissions(GroupTypeInterface $group_type): array {
    $permissions = [];

    /** @var \Drupal\group\Plugin\GroupContentEnablerCollection $plugins */
    $plugins = $group_type->getInstalledContentPlugins();

    foreach ($plugins as $plugin) {
      $plugin_id = $plugin->getPluginId();

      $permissions[] = "view $plugin_id entity";
      $permissions[] = "view $plugin_id content";
      $permissions[] = "create $plugin_id content";
      $permissions[] = "update own $plugin_id content";
      $permissions[] = "update any $plugin_id content";
      $permissions[] = "delete own $plugin_id content";
      $permissions[] = "delete any $plugin_id content";
    }

    return $permissions;
  }

}
