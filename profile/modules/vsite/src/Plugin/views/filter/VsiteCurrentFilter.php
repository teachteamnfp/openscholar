<?php

namespace Drupal\vsite\Plugin\views\filter;

use Drupal\views\Plugin\views\filter\FilterPluginBase;

/**
 *
 */
class VsiteCurrentFilter extends FilterPluginBase {

  /**
   *
   */
  public function query() {
    /** @var \Drupal\group\Entity\GroupInterface $group */
    if ($group = \Drupal::service('vsite.context_manager')->getActiveVsite()) {
      $this->query->addWhere('AND', 'gid', $group->id());
    }
  }

}
