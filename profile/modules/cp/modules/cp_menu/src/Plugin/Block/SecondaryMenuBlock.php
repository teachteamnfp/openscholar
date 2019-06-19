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

    $menu_name = self::SECONDARY_MENU;
    // Get the associated group menu for the current page.
    if ($this->vsite) {
      $secondary_menu_id = 'menu-secondary-' . $this->id;
      $vsite_menu = $this->vsite->getContent('group_menu:menu', ['entity_id_str' => $secondary_menu_id]);
      $menu_name = $vsite_menu ? $secondary_menu_id : self::SECONDARY_MENU;
    }
    // Render the menus.
    return $this->loadMenuTree($menu_name);
  }

}
