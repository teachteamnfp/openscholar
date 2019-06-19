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

    $menu_name = self::PRIMARY_MENU;

    // Get the associated group menu for the current page.
    if ($this->vsite) {
      $primary_menu_id = 'menu-primary-' . $this->id;
      $vsite_menu = $this->vsite->getContent('group_menu:menu', ['entity_id_str' => $primary_menu_id]);
      $menu_name = $vsite_menu ? $primary_menu_id : self::PRIMARY_MENU;
    }
    // Render the menus.
    return $this->loadMenuTree($menu_name);
  }

}
