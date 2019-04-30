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
 *   bundle = "events",
 *   id = "event"
 * )
 */
class EventsApp extends PluginBase implements AppInterface {

  /**
   * {@inheritdoc}
   */
  public function getGroupContentTypes() {
    return [
      'events',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->pluginDefinition['title'];
  }

  /**
   * {@inheritdoc}
   */
  public function getCreateLinks() {
    return [
      'event' => [
        'menu_name' => 'control-panel',
        'route_name' => 'node.add',
        'route_parameters' => ['node_type' => 'events'],
        'parent' => 'cp.content.add',
        'title' => $this->getTitle()->render(),
      ],
    ];
  }

}
