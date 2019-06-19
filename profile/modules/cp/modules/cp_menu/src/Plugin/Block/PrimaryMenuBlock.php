<?php

namespace Drupal\cp_menu\Plugin\Block;

/**
 * Provides a block for displaying group menus.
 *
 * @Block(
 *   id = "primarymenu",
 *   admin_label = @Translation("Primary menu")
 * )
 */
class PrimaryMenuBlock extends CpMenuBlockBase {

  /**
   * Primary menu will always be main by default.
   */
  const PRIMARY_MENU = 'main';

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Get the associated group menu for the current page.
    $menu_name = $this->getMenuName(self::PRIMARY_MENU);
    // Render the menus.
    return $this->loadMenuTree($menu_name);
  }

}
