<?php

namespace Drupal\os_widgets;

use Drupal\block_content\Entity\BlockContent;

/**
 * Interface CpSettingsManagerInterface.
 */
interface OsWidgetsInterface {

  /**
   * Builds block.
   *
   * @param array $variables
   *   Variables.
   * @param \Drupal\block_content\Entity\BlockContent $blockContent
   *   Content.
   *
   * @return array
   *   Renderable array.
   */
  public function buildBlock(array $variables, BlockContent $blockContent);

}
