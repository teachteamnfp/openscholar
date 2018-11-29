<?php

namespace Drupal\cp\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 *  Placeholders
 */
class CpController extends ControllerBase {

  /**
   * Just testing things
   */
  public function cpAdminMenuBlockPage() {
    return [
      '#markup' => 'Nothing here yet',
    ];
  }

  /**
   * An empty page for testing purposes
   */
  public function dummyPage() {
    return [
      '#markup' => 'Just a placeholder page',
    ];
  }

}
