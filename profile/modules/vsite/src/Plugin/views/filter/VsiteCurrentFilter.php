<?php

namespace Drupal\vsite\Plugin\views\filter;

use Drupal\views\Plugin\views\filter\FilterPluginBase;

/**
 * Filter a View for any content that's part of the current vsite.
 *
 * How to use:
 * 1. Create View for whatever content type you want
 * 2. Advanced > Relationships, Add Relationship to Group Content
 * 3. Add this filter. There are no settings to configure.
 * 4. Done.
 *
 * @ingroup views_filter_handlers
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
      $gids = _vsite_get_group_and_subsite_ids($group);
      $this->query->addWhere('vsite', 'gid', $gids, 'IN');
    }

    $this->displayHandler->display['cache_metadata']['contexts'][] = 'vsite';
  }

}
