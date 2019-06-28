<?php

namespace Drupal\os_software\Plugin\App;

use Drupal\Core\StringTranslation\StringTranslationTrait;
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
  use StringTranslationTrait;

  /**
   * Title for Software Project link.
   */
  const TITLE_PROJECT = 'Software Project';

  /**
   * Title for Software Release link.
   */
  const TITLE_RELEASE = 'Software Release';

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
        'title' => $this->t('@title', ['@title' => self::TITLE_PROJECT]),
      ],
      'software-release' => [
        'menu_name' => 'control-panel',
        'route_name' => 'node.add',
        'route_parameters' => ['node_type' => 'software_release'],
        'parent' => 'cp.content.add',
        'title' => $this->t('@title', ['@title' => self::TITLE_RELEASE]),
      ],
    ];
  }

}
