<?php

namespace Drupal\vsite\Entity;


use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Config\StorageInterface;

/**
 * Interface GroupPresetInterface.
 * @package Drupal\vsite\Entity
 */
interface GroupPresetInterface extends ConfigEntityInterface {

  /**
   * Returns the storage object that contains the config for this preset.
   *
   * @return StorageInterface
   */
  public function getPresetStorage() : StorageInterface;

  /**
   * Returns the tasks that should be executed when a Group is created.
   *
   * @return mixed
   */
  public function getCreationTasks();

}
