<?php

namespace Drupal\os;

/**
 * Interface for managing the list of angular modules to be added to the page.
 *
 * @package Drupal\os
 */
interface AngularModuleManagerInterface {

  /**
   * Add a module to the page.
   *
   * @param string $module
   *   The module we want on the page.
   */
  public function addModule(string $module);

  /**
   * Returns the list of all modules being added to the page.
   */
  public function getModules();

}
