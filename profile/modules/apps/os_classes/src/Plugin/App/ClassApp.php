<?php

namespace Drupal\os_classes\Plugin\App;

use Drupal\vsite\Plugin\AppPluginBase;

/**
 * Class app.
 *
 * @App(
 *   title = @Translation("Class"),
 *   canDisable = true,
 *   entityType = "node",
 *   bundle = [
 *    "class"
 *   ],
 *   id = "class"
 * )
 */
class ClassApp extends AppPluginBase {

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
