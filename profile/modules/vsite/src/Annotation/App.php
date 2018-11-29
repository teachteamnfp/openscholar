<?php

namespace Drupal\vsite\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * @Annotation
 */
class App extends Plugin {

  /**
   *
   */
  public function __construct($values) {
    parent::__construct($values);
  }

}
