<?php

namespace Drupal\os_widgets\Entity;

use Drupal\block_content\Entity\BlockContent;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
      $this->setVsiteContextManager(\Drupal::getContainer());
    }
    return $this->vsiteContextManager;
  }

  /**
   * Setter function for vsiteContextManager from container.
   */
  public function setVsiteContextManager(ContainerInterface $container) {
    $this->vsiteContextManager = $container->get('vsite.context_manager');
  }

}
