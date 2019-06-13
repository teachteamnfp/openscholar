<?php

namespace Drupal\group_entity\Plugin\GroupContentEnabler;

use Drupal\Component\Plugin\Derivative\DeriverBase;

/**
 * Derives enabler plugins for every entity type we want.
 *
 * Does not separate by bundle.
 */
class GroupEntityDeriver extends DeriverBase {

  /**
   * Creates all the plugins for entity_types we want.
   *
   * @inheritdoc
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    // @todo: Make this array configurable
    $entity_types = [
    // This key is found in the annotation for the entity_type, bundle_of.
      'media',
      'block_content',
      'taxonomy_term',
      'bibcite_reference',
      'redirect',
    ];
    foreach ($entity_types as $type_id) {
      $this->derivatives[$type_id] = [
        'entity_type_id' => $type_id,
        'label' => t('Group @type', ['@type' => $type_id]),
        'description' => t('Adds %type content to groups both publicly and privately.', ['%type' => $type_id]),
      ] + $base_plugin_definition;
    }

    return $this->derivatives;
  }

}
