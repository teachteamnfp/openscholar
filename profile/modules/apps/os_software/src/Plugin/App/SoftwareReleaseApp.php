<?php

namespace Drupal\os_software\Plugin\App;

use Drupal\vsite\Plugin\AppPluginBase;

/**
 * Software app.
 *
 * @App(
 *   title = @Translation("Software Release"),
 *   canDisable = true,
 *   entityType = "node",
 *   bundle = {
 *     "software_project"
 *   },
 *   id = "software_release"
 * )
 */
class SoftwareReleaseApp extends AppPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getCreateLinks() {
    return [
      'software-release' => [
        'menu_name' => 'control-panel',
        'route_name' => 'node.add',
        'route_parameters' => ['node_type' => 'software_release'],
        'parent' => 'cp.content.add',
        'title' => $this->getTitle(),
      ],
    ];
  }

}
