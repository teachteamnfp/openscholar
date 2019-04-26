<?php

namespace Drupal\os_profiles\Plugin\App;

use Drupal\Component\Plugin\PluginBase;
use Drupal\vsite\AppInterface;

/**
 * Profiles app.
 *
 * @App(
 *   title = @Translation("Profiles"),
 *   canDisable = true,
 *   entityType = "node",
 *   bundle = "person",
 *   id = "profiles"
 * )
 */
class ProfilesApp extends PluginBase implements AppInterface {

  /**
   * {@inheritdoc}
   */
  public function getGroupContentTypes() {
    return [
      'person',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->pluginDefinition['title'];
  }

}
