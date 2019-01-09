<?php

namespace Drupal\vsite\Plugin\views\filter;

use Drupal\views\Plugin\views\filter\FilterPluginBase;

/**
 * Filter a View for any subsite that's child of the current vsite.
 *
 * How to use:
 * 1. Create View for groups entity
 * 2. Advanced > Relationships, Add Relationship to "field_parent_site: Group"
 * 3. Set this Relationship to required (INNER JOIN)
 * 4. Add this filter. There are no settings to configure.
 * 5. Done.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("vsite_subsite_filter")
 */
class VsiteSubsiteFilter extends FilterPluginBase {

  /**
   * Alter the query.
   */
  public function query() {
    /** @var \Drupal\group\Entity\GroupInterface $group */
    if ($group = \Drupal::service('vsite.context_manager')->getActiveVsite()) {
      $this->query->addWhere('vsite', 'field_parent_site_target_id', $group->id());
      $this->displayHandler->display['cache_metadata']['contexts'][] = 'vsite:' . $group->id();
    }
    else {
      $this->displayHandler->display['cache_metadata']['contexts'][] = 'vsite:none';
    }
  }

}
