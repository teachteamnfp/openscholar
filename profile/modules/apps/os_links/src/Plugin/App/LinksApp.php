<?php

namespace Drupal\links\Plugin\App;

use Drupal\Component\Plugin\PluginBase;
use Drupal\vsite\AppInterface;

/**
 * @App(
 *   title = @Translation("Links"),
 *   canDisable = true,
 *   entityType = "node",
 *   bundle = "link"
 * )
 *
 * Plugin for the Links App
 */
class LinksApp extends PluginBase implements AppInterface {

  /**
   * {@inheritdoc}
   */
  public function getGroupContentTypes() {
    return [
      'link',
    ];
  }

}
