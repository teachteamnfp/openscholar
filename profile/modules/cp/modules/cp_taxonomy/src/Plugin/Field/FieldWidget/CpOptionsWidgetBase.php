<?php

namespace Drupal\cp_taxonomy\Plugin\Field\FieldWidget;

use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\OptionsWidgetBase;
use Drupal\Core\Form\OptGroup;

/**
 * Base class for the 'cp_options_*' widgets.
 */
abstract class CpOptionsWidgetBase extends OptionsWidgetBase {

  /**
   * {@inheritdoc}
   */
  protected function getOptions(FieldableEntityInterface $entity) {
    if (!isset($this->options)) {
      $options_provider = $this->fieldDefinition
        ->getFieldStorageDefinition()
        ->getOptionsProvider('target_id', $entity);
      $field_definition = $options_provider->getFieldDefinition();
      $options = \Drupal::service('plugin.manager.entity_reference_selection')->getSelectionHandler($field_definition, $entity)->getReferenceableEntities();

      $options = ['_none' => $this->t('- None -')] + $options;

      array_walk_recursive($options, [$this, 'sanitizeLabel']);

      $this->options = $options;
    }
    return $this->options;
  }

  /**
   * {@inheritdoc}
   */
  protected function getSelectedOptions(FieldItemListInterface $items) {
    // We need to check against a flat list of options.
    $flat_options = OptGroup::flattenOptions($this->getOptions($items->getEntity()));

    $selected_options = [];
    foreach ($items as $item) {
      $value = $item->target_id;
      // Keep the value if it actually is in the list of options (needs to be
      // checked against the flat list).
      if (isset($flat_options[$value])) {
        $selected_options[] = $value;
      }
    }

    return $selected_options;
  }

}
