<?php

namespace Drupal\vsite\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * A simple test page to demonstrate that vsite-level config works.
 */
class VsiteExampleController extends ControllerBase {

  /**
   * The page function.
   */
  public function content() {

    $config = $this->config('vsite.test_settings');
    $build = [
      '#markup' => $config->get('checkbox'),
    ];

    return $build;
  }

}
