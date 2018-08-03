<?php
/**
 * Created by PhpStorm.
 * User: New User
 * Date: 8/3/2018
 * Time: 2:43 PM
 */

namespace Drupal\vsite\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * @Annotation
 */
class App extends Plugin {

  public function __construct($values) {
    parent::__construct($values);
  }
}