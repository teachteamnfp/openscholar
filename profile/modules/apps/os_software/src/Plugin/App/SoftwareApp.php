<?php

namespace Drupal\os_software\Plugin\App;

use Drupal\vsite\Plugin\AppPluginBase;

/**
 * Software app.
 *
 * @App(
 *   title = @Translation("Software"),
 *   canDisable = true,
 *   entityType = "node",
 *   bundle = {
 *     "software_project",
 *     "software_release"
 *   },
 *   id = "software"
 * )
 */
class SoftwareApp extends AppPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getCreateLinks() {
    return [
      'software-project' => [
        'menu_name' => 'control-panel',
        'route_name' => 'node.add',
        'route_parameters' => ['node_type' => 'software_project'],
        'parent' => 'cp.content.add',
        'title' => $this->getTitle(),
      ],
    ];
  }

}
