<?php

namespace Drupal\os_links\Plugin\App;

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
      'link' => [
        'menu_name' => 'control-panel',
        'route_name' => 'node.add',
        'route_parameters' => ['node_type' => 'link'],
        'parent' => 'cp.content.add',
        'title' => $this->getTitle()->render(),
      ],
    ];
  }

}
