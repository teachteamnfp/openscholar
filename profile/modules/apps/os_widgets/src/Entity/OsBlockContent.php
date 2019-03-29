<?php

namespace Drupal\os_widgets\Entity;

use Drupal\block_content\Entity\BlockContent;

/**
 * Extend original BlockContent entity with extra method.
 */
class OsBlockContent extends BlockContent {

  /**
   * Get current vsite id or none string.
   *
   * @return string
   *   Vsite id.
   */
  public function getVsiteCacheTag(): string {
    $prefix = 'block_content_entity_vsite:';
    $vsite_context = \Drupal::service('vsite.context_manager');
    if ($vsite_context->getActiveVsite()) {
      return $prefix . $vsite_context->getActiveVsite()->id();
    }
    return $prefix . 'none';
  }

}
