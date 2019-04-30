<?php

namespace Drupal\os_profiles\Plugin\App;

use Drupal\Component\Plugin\PluginBase;
use Drupal\vsite\AppInterface;

/**
 * Profiles app.
 *
 * @App(
 *   title = @Translation("Profiles"),
 *   canDisable = true,
 *   entityType = "node",
 *   bundle = "person",
 *   id = "profiles"
 * )
 */
class ProfilesApp extends PluginBase implements AppInterface {

  /**
   * {@inheritdoc}
   */
  public function getGroupContentTypes() {
    return [
      'person',
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
      'person' => [
        'menu_name' => 'control-panel',
        'route_name' => 'node.add',
        'route_parameters' => ['node_type' => 'person'],
        'parent' => 'cp.content.add',
        'title' => $this->getTitle()->render(),
      ],
    ];
  }

}
