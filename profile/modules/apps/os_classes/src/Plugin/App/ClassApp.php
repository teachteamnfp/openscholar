<?php

namespace Drupal\os_classes\Plugin\App;

use Drupal\Component\Plugin\PluginBase;
use Drupal\vsite\AppInterface;

/**
 * Class app.
 *
 * @App(
 *   title = @Translation("Class"),
 *   canDisable = true,
 *   entityType = "node",
 *   bundle = "class",
 *   id = "class"
 * )
 */
class ClassApp extends PluginBase implements AppInterface {

  /**
   * {@inheritdoc}
   */
  public function getGroupContentTypes() {
    return [
      'class',
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
      'class' => [
        'menu_name' => 'control-panel',
        'route_name' => 'node.add',
        'route_parameters' => ['node_type' => 'class'],
        'parent' => 'cp.content.add',
        'title' => $this->getTitle()->render()
      ]
    ];
  }

}
