<?php

namespace Drupal\vsite\Controller;


use Drupal\Core\Controller\ControllerBase;

class VsiteExampleController extends ControllerBase {

  public function content() {
    $build = [
      '#markup' => $this->t('Hello World!'),
    ];

    return $build;
  }
}