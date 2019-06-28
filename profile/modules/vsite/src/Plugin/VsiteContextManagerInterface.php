<?php

namespace Drupal\vsite\Plugin;

use Drupal\Core\Render\BubbleableMetadata;
use Drupal\group\Entity\GroupInterface;

/**
 * Interface for the VsiteContextManager class.
 */
interface VsiteContextManagerInterface {

  /**
   * Activate the vsite represented by the given group.
   *
   * @param \Drupal\group\Entity\GroupInterface $group
   *   The group to be activated.
   */
  public function activateVsite(GroupInterface $group);

  /**
   * Return the active vsite.
   *
   * @return \Drupal\group\Entity\GroupInterface|null
   *   The group if it is active, otherwise NULL.
   */
  public function getActiveVsite() : ?GroupInterface;

  /**
   * Return the purl for the active vsite.
   */
  public function getActivePurl();

  /**
   * Get an absolute url a vsite.
   *
   * @param string $path
   *   The URL path that is requested.
   * @param GroupInterface $group
   *   The group we want to fetch the url for.
   * @param BubbleableMetadata $bubbleable_metadata
   *   Caching metadata.
   */
  public function getAbsoluteUrl(string $path = '', GroupInterface $group, BubbleableMetadata $bubbleable_metadata);

}
