<?php
/**
 * Created by PhpStorm.
 * User: New User
 * Date: 9/17/2018
 * Time: 2:22 PM
 */

namespace Drupal\vsite\Config;


use Drupal\Core\Config\StorageInterface;

interface HierarchicalStorageInterface extends StorageInterface {

  public function addStorage(StorageInterface $storage, $weight);
}