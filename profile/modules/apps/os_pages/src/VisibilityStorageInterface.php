<?php

namespace Drupal\os_pages;

use Drupal\block_visibility_groups\BlockVisibilityGroupInterface;

/**
 * Handles storage operations of block visibility group.
 */
interface VisibilityStorageInterface {

  /**
   * Creates a new block visibility group.
   *
   * @param array $values
   *   Init values of the entity.
   * @param array $conditions
   *   Visibility group conditions.
   *
   * @return \Drupal\block_visibility_groups\BlockVisibilityGroupInterface
   *   The newly created entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function create(array $values, array $conditions) : BlockVisibilityGroupInterface;

}
