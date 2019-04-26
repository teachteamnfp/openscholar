<?php

namespace Drupal\os_events\Plugin\App;

use Drupal\Component\Plugin\PluginBase;
use Drupal\vsite\AppInterface;

/**
 * Events app.
 *
 * @App(
 *   title = @Translation("Events"),
 *   canDisable = true,
 *   entityType = "node",
 *   bundle = "event",
 *   id = "event"
 * )
 */
class EventsApp extends PluginBase implements AppInterface {

  /**
   * {@inheritdoc}
   */
  public function getGroupContentTypes() {
    return [
      'event',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->pluginDefinition['title'];
  }

}
