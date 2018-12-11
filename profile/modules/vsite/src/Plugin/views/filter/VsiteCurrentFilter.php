<?php

namespace Drupal\vsite\Plugin\views\filter;

use Drupal\views\Plugin\views\filter\FilterPluginBase;

/**
 * Filter a View for any content that's part of the current vsite.
 *
 * @ViewsFilter("vsite_current_filter")
 */
class VsiteCurrentFilter extends FilterPluginBase {

  /**
   * Alter the query.
   */
  public function query() {
    /** @var \Drupal\group\Entity\GroupInterface $group */
    if ($group = \Drupal::service('vsite.context_manager')->getActiveVsite()) {
      $this->query->addWhere('AND', 'gid', $group->id());
    }
  }

}
