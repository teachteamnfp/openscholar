<?php
/**
 * Created by PhpStorm.
 * User: New User
 * Date: 10/19/2018
 * Time: 4:37 PM
 */

namespace Drupal\group_entity\Plugin\GroupContentEnabler;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

class GroupEntityDeriver extends DeriverBase {

  /**
   * {@inheritdoc}.
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $entity_types = [ // @todo: Make this array configurable
      'media', // this key is found in the annotation for the entity_type, bundle_of
      'block_content',
      'taxonomy_term'
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