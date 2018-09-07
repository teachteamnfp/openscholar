<?php

namespace Drupal\vsite\Controller;


use Drupal\Core\Controller\ControllerBase;

class VsiteExampleController extends ControllerBase {

  public function content() {

    $config = $this->config('vsite.test_settings');
    dvm($config->get('checkbox'));
    $build = [
      '#markup' => $config->get('checkbox'),
    ];

    return $build;
  }
}