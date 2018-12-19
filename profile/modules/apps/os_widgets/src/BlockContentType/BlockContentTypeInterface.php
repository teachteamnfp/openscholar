<?php

namespace Drupal\os_widgets\BlockContentType;


interface BlockContentTypeInterface {

  /**
   * @param array $variables
   *
   * @param \Drupal\block_content\Entity\BlockContent $blockContent
   *
   * @return array
   */
  function buildBlock($variables, $blockContent);
}
