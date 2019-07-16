<?php

namespace Drupal\os;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\group\Entity\GroupContentType;
use Drupal\user\EntityOwnerInterface;
use Drupal\vsite\Plugin\VsiteContextManagerInterface;

/**
 * Provides access check helpers for entity CRUD global paths.
 */
final class AccessHelper implements AccessHelperInterface {

  /**
   * Vsite context manager.
   *
   * @var \Drupal\vsite\Plugin\VsiteContextManagerInterface
   */
  protected $vsiteContextManager;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Creates a new AccessHelper object.
   *
   * @param \Drupal\vsite\Plugin\VsiteContextManagerInterface $vsite_context_manager
   *   Vsite context manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   */
  public function __construct(VsiteContextManagerInterface $vsite_context_manager, EntityTypeManagerInterface $entity_type_manager) {
    $this->vsiteContextManager = $vsite_context_manager;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function checkCreateAccess(AccountInterface $account, string $plugin_id): AccessResultInterface {
    /** @var \Drupal\group\Entity\GroupInterface|null $vsite */
    $vsite = $this->vsiteContextManager->getActiveVsite();

    // Let the access stack handle this case.
    if (!$vsite) {
      return AccessResult::neutral();
    }

    // Only act if there are group content types for this node type.
    $group_content_types = GroupContentType::loadByContentPluginId($plugin_id);
    if (empty($group_content_types)) {
      return AccessResult::neutral();
    }

    // Pass the judgement here.
    if ($vsite->hasPermission("create $plugin_id entity", $account) && $vsite->hasPermission("create $plugin_id content", $account)) {
      return AccessResult::allowed();
    }

    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  public function checkAccess(EntityInterface $entity, string $operation, AccountInterface $account): AccessResultInterface {
    if (!($entity instanceof EntityOwnerInterface)) {
      return AccessResult::neutral();
    }

    $plugin_id = "group_entity:{$entity->getEntityTypeId()}";

    if ($entity->getEntityTypeId() === 'node') {
      $plugin_id = "group_node:{$entity->bundle()}";
    }

    // Only act if there are group content types for this plugin.
    $group_content_types = GroupContentType::loadByContentPluginId($plugin_id);
    if (empty($group_content_types)) {
      return AccessResult::neutral();
    }

    // Load all the group content for this node.
    $group_contents = $this->entityTypeManager
      ->getStorage('group_content')
      ->loadByProperties([
        'type' => array_keys($group_content_types),
        'entity_id' => $entity->id(),
      ]);

    // If the entity does not belong to any group, we have nothing to say.
    if (empty($group_contents)) {
      return AccessResult::neutral();
    }

    /** @var \Drupal\group\Entity\GroupInterface[] $groups */
    $groups = [];
    foreach ($group_contents as $group_content) {
      /** @var \Drupal\group\Entity\GroupContentInterface $group_content */
      $group = $group_content->getGroup();
      $groups[$group->id()] = $group;
    }

    switch ($operation) {
      case 'update':
      case 'delete':
        foreach ($groups as $group) {
          if ($group->hasPermission("$operation any $plugin_id entity", $account)) {
            return AccessResult::allowed();
          }

          if ($group->hasPermission("$operation own $plugin_id entity", $account) &&
            ($account->id() === $entity->getOwner()->id())) {
            return AccessResult::allowed();
          }
        }

        break;
    }

    return AccessResult::neutral();
  }

}
