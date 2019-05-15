<?php

namespace Drupal\os;

/**
 * Interface MenuHelperInterface.
 *
 * @package Drupal\os
 */
interface MenuHelperInterface {

  /**
   * Get Menus.
   *
   * @return array
   *   Array of keyed menus.
   */
  public function osGetMenus() : array;

}
