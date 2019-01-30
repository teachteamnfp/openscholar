<?php

namespace Drupal\os;


class OsAngularModuleManager implements AngularModuleManagerInterface {

  protected $modules;

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