<?php

namespace Drupal\os_widgets\Entity;

use Drupal\block_content\Entity\BlockContent;
use Drupal\vsite\Plugin\VsiteContextManagerInterface;

/**
 * Extend original BlockContent entity with extra method.
 */
class OsBlockContent extends BlockContent {

  private $vsiteContextManager;

  /**
   * Get current vsite id or none string.
   *
   * @return string
   *   Vsite id.
   */
  public function getVsiteCacheTag(): string {
    $prefix = 'block_content_entity_vsite:';
    $vsite_context = $this->getVsiteContextManager();
    if ($vsite_context->getActiveVsite()) {
      return $prefix . $vsite_context->getActiveVsite()->id();
    }
    return $prefix . 'none';
  }

  /**
   * Getter function for vsiteContextManager.
   */
  public function getVsiteContextManager() {
    if (empty($this->vsiteContextManager)) {
      /** @var \Drupal\vsite\Plugin\VsiteContextManagerInterface $vsite_context_manager */
      $vsite_context_manager = \Drupal::getContainer()->get('vsite.context_manager');
      $this->setVsiteContextManager($vsite_context_manager);
    }
    return $this->vsiteContextManager;
  }

  /**
   * Setter function for vsiteContextManager.
   */
  public function setVsiteContextManager(VsiteContextManagerInterface $vsite_context_manager) {
    $this->vsiteContextManager = $vsite_context_manager;
  }

}
