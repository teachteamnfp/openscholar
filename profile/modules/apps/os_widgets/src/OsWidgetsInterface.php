<?php

namespace Drupal\os_widgets;

use Drupal\block_content\Entity\BlockContent;

/**
 * Interface OsWidgetsInterface.
 */
interface OsWidgetsInterface {

  /**
   * Builds block.
   *
   * @param array &$build
   *   Build Items.
   * @param \Drupal\block_content\Entity\BlockContent $blockContent
   *   Content.
   *
   * @return array
   *   Renderable array.
   */
  public function buildBlock(array &$build, BlockContent $blockContent);

}
