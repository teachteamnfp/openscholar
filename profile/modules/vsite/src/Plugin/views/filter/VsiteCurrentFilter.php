<?php

/**
 * Filters entities on the current active vsite
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("vsite_current_filter")
 *
 * How to use:
 * 1. Create View for whatever content type you want
 * 2. Advanced > Relationships, Add Relationship to Group Content
 * 3. Add this filter. There are no settings to configure.
 * 4. Done
 */

namespace Drupal\vsite\Plugin\views\filter;

use Drupal\group\Entity\GroupInterface;
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

      $this->displayHandler->display['cache_metadata']['contexts'][] = 'vsite:'.$group->id();
    }
    else {
      $this->displayHandler->display['cache_metadata']['contexts'][] = 'vsite:none';
    }
  }

}
