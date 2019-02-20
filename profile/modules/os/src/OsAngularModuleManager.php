<?php

namespace Drupal\os;

/**
 * Manages the angular modules being added to the page.
 */
class OsAngularModuleManager implements AngularModuleManagerInterface {

  protected $modules;

  /**
   * Constructor.
   */
  public function __construct() {
    $this->modules = [];
  }

  /**
   * {@inheritdoc}
   */
  public function addModule(string $module) {
    if (!in_array($module, $this->modules)) {
      $this->modules[] = $module;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getModules() {
    return $this->modules;
  }

}
