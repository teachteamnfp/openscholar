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

  /**
   * Creates new vsite specific menu with limited/no links.
   *
   * @param \Drupal\group\Entity\GroupInterface $vsite
   *   The vsite in context.
   * @param bool $secondary
   *   If secondary menu's reset button is clicked.
   */
  public function resetVsiteMenus(GroupInterface $vsite, $secondary = FALSE) : void;

}
