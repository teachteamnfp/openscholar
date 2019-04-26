<?php

namespace Drupal\os_software\Plugin\App;

use Drupal\Component\Plugin\PluginBase;
use Drupal\vsite\AppInterface;

/**
 * Software app.
 *
 * @App(
 *   title = @Translation("Software"),
 *   canDisable = true,
 *   entityType = "node",
 *   bundle = "software_project",
 *   id = "software"
 * )
 */
class SoftwareApp extends PluginBase implements AppInterface {

  /**
   * {@inheritdoc}
   */
  public function getGroupContentTypes() {
    return [
      'software_project',
      'software_release'
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->pluginDefinition['title'];
  }

}
