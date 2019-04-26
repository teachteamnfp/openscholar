<?php

namespace Drupal\os_presentations\Plugin\App;

use Drupal\Component\Plugin\PluginBase;
use Drupal\vsite\AppInterface;

/**
 * Presentationss app.
 *
 * @App(
 *   title = @Translation("Presentations"),
 *   canDisable = true,
 *   entityType = "node",
 *   bundle = "presentation",
 *   id = "presentations"
 * )
 */
class PresentationsApp extends PluginBase implements AppInterface {

  /**
   * {@inheritdoc}
   */
  public function getGroupContentTypes() {
    return [
      'presentation',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->pluginDefinition['title'];
  }

}
