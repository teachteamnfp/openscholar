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

  /**
   * Save a value to a specific level.
   *
   * @param string $name
   *   Name of config item.
   * @param mixed $value
   *   Value of config item.
   * @param $level
   *   The level being being saved to
   *
   * @return void
   */
  public function saveTolevel($name, $value, $level);

  /**
   * Override the level that writes should occur at.
   *
   * @param $level
   *   Level to write to.
   *
   * @return void
   */
  public function overrideWriteLevel($level);

  /**
   * Clear any write level overrides.
   *
   * @return void
   */
  public function clearWriteOverride();

}
