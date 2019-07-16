<?php

namespace Drupal\vsite_site_creation;

use Drupal\Core\Url;
use Drupal\user\ToolbarLinkBuilder as Original;

/**
 * Decorator for the user Toolbar Builder.
 */
class ToolbarLinkBuilder extends Original {

  /**
   * {@inheritdoc}
   */
  public function renderToolbarLinks() {
    $build = parent::renderToolbarLinks();

    $build['#links']['site-create'] = [
      'title' => $this->t('Create site'),
      'url' => Url::fromRoute('<none>'),
      'attributes' => [
        'title' => $this->t('Create site'),
        'site-creation-form' => '',
      ],
    ];
    $build['#attached']['library'][] = 'vsite_site_creation/site_creation';

    return $build;
  }

}
