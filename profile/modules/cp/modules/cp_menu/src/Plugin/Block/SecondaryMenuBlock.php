<?php

namespace Drupal\cp_menu\Plugin\Block;

/**
 * Provides a block for displaying group menus.
 *
 * @Block(
 *   id = "secondarymenu",
 *   admin_label = @Translation("Secondary menu")
 * )
 */
class SecondaryMenuBlock extends CpMenuBlockBase {

  /**
   * Secondary menu will always be footer by default.
   */
  const SECONDARY_MENU = 'footer';

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Get the associated group menu for the current page.
    $menu_name = $this->getMenuName(self::SECONDARY_MENU);
    // Render the menus.
    return $this->loadMenuTree($menu_name);
  }

}
