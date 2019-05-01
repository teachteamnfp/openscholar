<?php

namespace Drupal\os_links\Plugin\App;

use Drupal\vsite\Plugin\AppPluginBase;

/**
 * Plugin for the Links App.
 *
 * @App(
 *   title = @Translation("Links"),
 *   canDisable = true,
 *   entityType = "node",
 *   bundle = {
 *     "link",
 *   },
 *   id = "links"
 * )
 */
class LinksApp extends AppPluginBase {

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
