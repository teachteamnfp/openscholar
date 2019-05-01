<?php

namespace Drupal\os_faq\Plugin\App;

use Drupal\vsite\Plugin\AppPluginBase;

/**
 * FAQ app.
 *
 * @App(
 *   title = @Translation("FAQ"),
 *   canDisable = true,
 *   entityType = "node",
 *   bundle =
 *    "faq"
 *    ],
 *   id = "faq"
 * )
 */
class FAQApp extends AppPluginBase {

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
