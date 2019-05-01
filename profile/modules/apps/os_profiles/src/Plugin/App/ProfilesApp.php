<?php

namespace Drupal\os_profiles\Plugin\App;

use Drupal\vsite\Plugin\AppPluginBase;

/**
 * Profiles app.
 *
 * @App(
 *   title = @Translation("Profiles"),
 *   canDisable = true,
 *   entityType = "node",
 *   bundle = {
 *     "person"
 *   },
 *   id = "profiles"
 * )
 */
class ProfilesApp extends AppPluginBase {

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
