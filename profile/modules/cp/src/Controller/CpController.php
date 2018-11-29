<?php

namespace Drupal\cp\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 *
 */
class CpController extends ControllerBase {

  /**
   *
   */
  public function cpAdminMenuBlockPage() {
    return [
      '#markup' => 'Nothing here yet',
    ];
  }

  /**
   *
   */
  public function dummyPage() {
    return [
      '#markup' => 'Just a placeholder page',
    ];
  }

}
