<?php

namespace Drupal\os_presentations\Plugin\App;

use Drupal\Component\Plugin\PluginBase;
use Drupal\vsite\AppInterface;

/**
 * Presentationss app.
 *
 * @App(
 *   title = @Translation("Presentation"),
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

  /**
   * {@inheritdoc}
   */
  public function getCreateLinks() {
    return [
      'presentation' => [
        'menu_name' => 'control-panel',
        'route_name' => 'node.add',
        'route_parameters' => ['node_type' => 'presentation'],
        'parent' => 'cp.content.add',
        'title' => $this->getTitle()->render()
      ]
    ];
  }

}
