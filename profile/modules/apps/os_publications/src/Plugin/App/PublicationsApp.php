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

  /**
   * {@inheritdoc}
   */
  public function getCreateLinks() {
    return [
      'publication' => [
        'menu_name' => 'control-panel',
        'route_name' => 'entity.bibcite_reference.add_page',
        'parent' => 'cp.content.add',
        'title' => $this->getTitle()->render(),
      ],
    ];
  }

}
