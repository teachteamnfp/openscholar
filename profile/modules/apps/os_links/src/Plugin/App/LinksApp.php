<?php

namespace Drupal\links\Plugin\App;

use Drupal\Component\Plugin\PluginBase;
use Drupal\vsite\AppInterface;

/**
 * @App(
 *   title = @Translation("Links"),
 *   canDisable = true,
 *   entityType = "node",
 *   bundle = "link",
 *   id = "links"
 * )
 */
class LinksApp extends PluginBase implements AppInterface {


  public function getGroupContentTypes() {
    return array(
      'link'
    );
  }

  /**
   * @inheritDoc
   */
  public function getTitle () {
    dpm($this->pluginDefinition);
    return $this->pluginDefinition['title'];
  }
}