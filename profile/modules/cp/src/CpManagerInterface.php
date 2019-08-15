<?php

namespace Drupal\cp;

/**
 * CpManager service.
 */
interface CpManagerInterface {

  /**
   * Loads the contents of a menu block.
   *
   * This function is often a destination for these blocks.
   * For example, 'admin/structure/types' needs to have a destination to be
   * valid in the Drupal menu system, but too much information there might be
   * hidden, so we supply the contents of the block.
   *
   * @param string $menu_name
   *   The menu name.
   *
   * @return array
   *   A render array suitable for
   *   \Drupal\Core\Render\RendererInterface::render().
   *
   * @see \Drupal\system\SystemManager::getBlockContents
   */
  public function getBlockContents(string $menu_name): array;

}
