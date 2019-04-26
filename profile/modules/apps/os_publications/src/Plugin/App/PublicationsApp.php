<?php

namespace Drupal\os_publications\Plugin\App;

use Drupal\Component\Plugin\PluginBase;
use Drupal\vsite\AppInterface;

/**
 * Publications app.
 *
 * @App(
 *   title = @Translation("Publications"),
 *   canDisable = true,
 *   entityType = "reference",
 *   id = "publications"
 * )
 */
class PublicationsApp extends PluginBase implements AppInterface {

  /**
   * {@inheritdoc}
   */
  public function getGroupContentTypes() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->pluginDefinition['title'];
  }

}
