<?php

namespace Drupal\os_pages\Plugin\App;

use Drupal\Component\Plugin\PluginBase;
use Drupal\vsite\AppInterface;

/**
 * Pages app.
 *
 * @App(
 *   title = @Translation("Pages"),
 *   canDisable = false,
 *   entityType = "node",
 *   bundle = "page",
 *   id = "page"
 * )
 */
class PagesApp extends PluginBase implements AppInterface {

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
      'page' => [
        'menu_name' => 'control-panel',
        'route_name' => 'node.add',
        'route_parameters' => ['node_type' => 'page'],
        'parent' => 'cp.content.add',
        'title' => $this->getTitle()->render()
      ]
    ];
  }

}
