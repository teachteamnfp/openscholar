<?php

namespace Drupal\links\Plugin\App;

use Drupal\Component\Plugin\PluginBase;
use Drupal\vsite\AppInterface;

/**
 * @App(
 *   title = @Translation("Links"),
 *   canDisable = true
 * )
 */
class LinksApp extends PluginBase implements AppInterface {


  public function getGroupContentTypes() {
    return array(
      'link'
    );
  }
}