<?php

namespace Drupal\os_pages;

use Drupal\block_visibility_groups\BlockVisibilityGroupInterface;
use Drupal\block_visibility_groups\Entity\BlockVisibilityGroup;

/**
 * Handles storage operations of block visibility group.
 */
final class VisibilityStorage implements VisibilityStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function create(array $values, array $conditions): BlockVisibilityGroupInterface {
    /** @var \Drupal\block_visibility_groups\BlockVisibilityGroupInterface $visbility_group */
    $visibility_group = BlockVisibilityGroup::create($values);

    $visibility_group->save();

    foreach ($conditions as $condition) {
      $visibility_group->addCondition($condition);
    }

    $visibility_group->save();

    return $visibility_group;
  }

}
