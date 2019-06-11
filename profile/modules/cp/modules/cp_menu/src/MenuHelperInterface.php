<?php

namespace Drupal\cp_menu;

use Drupal\group\Entity\GroupInterface;

/**
 * MenuHelperInterface.
 */
interface MenuHelperInterface {

  /**
   * Creates new vsite specific menus and returns the primary menu tree.
   *
   * @param \Drupal\group\Entity\GroupInterface $vsite
   *   The vsite in context.
   *
   * @return array
   *   The menu tree.
   */
  public function createVsiteMenus(GroupInterface $vsite) : array;

}
