<?php

namespace Drupal\vsite\Config;

use Drupal\Core\Config\StorageInterface;

/**
 * Interface class for the HierarchicalStorage functionality.
 *
 * This allows ConfigStorage objects to be stacked from most important to least, and allows a ConfigStorage to
 *   inherit config objects it hasn't defined itself.
 */
interface HierarchicalStorageInterface extends StorageInterface {

  /**
   * Adds a storage to stack, and the given weight.
   * Higher weights are more important.
   *
   * @param \Drupal\Core\Config\StorageInterface $storage
   * @param int weight
   */
  public function addStorage(StorageInterface $storage, $weight);

}
