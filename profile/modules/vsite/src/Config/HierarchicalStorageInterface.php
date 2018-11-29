<?php

namespace Drupal\vsite\Config;

use Drupal\Core\Config\StorageInterface;

/**
 *
 */
interface HierarchicalStorageInterface extends StorageInterface {

  /**
   *
   */
  public function addStorage(StorageInterface $storage, $weight);

}
