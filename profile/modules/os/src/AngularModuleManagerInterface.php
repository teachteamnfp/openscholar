<?php

namespace Drupal\os;

/**
 * Interface for managing the list of angular modules to be added to the page
 * @package Drupal\os
 */
interface AngularModuleManagerInterface {

  public function addModule(string $module);

  public function getModules();
}