<?php

namespace Drupal\os_presentations\Plugin\App;

use Drupal\vsite\Plugin\AppPluginBase;

/**
 * Presentations app.
 *
 * @App(
 *   title = @Translation("Presentation"),
 *   canDisable = true,
 *   entityType = "node",
 *   bundle = [
 *     "presentation"
 *   ],
 *   id = "presentations"
 * )
 */
class PresentationsApp  extends AppPluginBase {

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
