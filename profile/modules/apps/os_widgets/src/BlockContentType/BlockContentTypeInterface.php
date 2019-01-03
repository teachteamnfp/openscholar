<?php

namespace Drupal\os_widgets\BlockContentType;

use Drupal\block_content\Entity\BlockContent;

/**
 * Interface BlockContentTypeInterface.
 */
interface BlockContentTypeInterface {

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
