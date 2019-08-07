<?php

namespace Drupal\os_software;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\node\NodeInterface;

/**
 * Helper functions to handle software project and release.
 */
class OsSoftwareHelper implements OsSoftwareHelperInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function prepareReleaseTitle(NodeInterface $release_node) : string {
    $project_title = 'Project Release';

    $projects = $release_node->get('field_software_project')->referencedEntities();
    if (!empty($projects)) {
      $project_node = array_shift($projects);
      $project_title = $project_node->label();
    }

    /** @var \Drupal\Core\Field\FieldItemList $version_field */
    $version_field = $release_node->get('field_software_version');
    $version = trim($version_field->getString());

    $title_parts = [
      $project_title,
      $version,
    ];

    return implode(' ', $title_parts);
  }

}
