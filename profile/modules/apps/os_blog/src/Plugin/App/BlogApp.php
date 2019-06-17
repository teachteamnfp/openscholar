<?php

namespace Drupal\os_blog\Plugin\App;

use Drupal\vsite\Plugin\AppPluginBase;

/**
 * Bog app.
 *
 * @App(
 *   title = @Translation("Blog"),
 *   canDisable = true,
 *   entityType = "node",
 *   bundle = "blog",
 *   id = "blog"
 * )
 */
class BlogApp extends AppPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getGroupContentTypes() {
    return [
      'blog',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCreateLinks() {
    return [
      'blog' => [
        'menu_name' => 'control-panel',
        'route_name' => 'node.add',
        'route_parameters' => ['node_type' => 'blog'],
        'parent' => 'cp.content.add',
        'title' => $this->getTitle(),
      ],
    ];
  }

}
