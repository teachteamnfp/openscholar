<?php

namespace Drupal\os_pages\Plugin\App;

use Drupal\vsite\Plugin\AppPluginBase;

/**
 * Pages app.
 *
 * @App(
 *   title = @Translation("Pages"),
 *   canDisable = false,
 *   entityType = "node",
 *   bundle = {
 *     "page"
 *   },
 *   id = "page"
 * )
 */
class PagesApp extends AppPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getCreateLinks() {
    $definition = $this->getPluginDefinition();
    $links = [];

    foreach ($definition['bundle'] as $b) {
      $links[$b] = [
        'menu_name' => 'control-panel',
        'route_name' => 'node.add',
        'route_parameters' => ['node_type' => $b],
        'parent' => 'cp.content.add',
        'title' => $this->getTitle(),
      ];
    }

    return $links;
  }

}
