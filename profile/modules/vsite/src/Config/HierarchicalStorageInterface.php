<?php

namespace Drupal\vsite\Config;

use Drupal\Core\Config\StorageInterface;

/**
 * Interface class for the HierarchicalStorage functionality.
 *
 * This allows ConfigStorage objects to be stacked from most important to least,
 *   and allows a ConfigStorage to inherit config objects
 *   it hasn't defined itself.
 */
interface HierarchicalStorageInterface extends StorageInterface {

  /**
   * Adds a storage to stack with the given weight.
   *
   * @param \Drupal\Core\Config\StorageInterface $storage
   *   The storage being added to the stack.
   * @param int $weight
   *   The weight of the storage. Higher weights are read from first.
   */
  public function addStorage(StorageInterface $storage, $weight);

  /**
   * List all results from a certain level.
   *
   * @param string $prefix
   *   Config name prefix to search for.
   * @param int $level
   *   The level of storage we want to pull from.
   *
   * @return string[]
   *   All matching config names from the given storage level.
   */
  public function listAllFromLevel($prefix = '', $level = HierarchicalStorage::GLOBAL_STORAGE);

}
