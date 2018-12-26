<?php

namespace Drupal\links\Plugin\App;

use Drupal\Component\Plugin\PluginBase;
use Drupal\vsite\AppInterface;

/**
 * Plugin for the Links App.
 *
 * @App(
 *   title = @Translation("Links"),
 *   canDisable = true,
 *   entityType = "node",
 *   bundle = "link",
 *   id = "links"
 * )
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

  /**
   * Returns title.
   *
   * @return string
   *   The title.
   */
  public function getTitle() {
    return $this->pluginDefinition['title'];
  }

}
