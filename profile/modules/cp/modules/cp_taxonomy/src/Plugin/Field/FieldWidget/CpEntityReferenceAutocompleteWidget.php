<?php

namespace Drupal\cp_taxonomy\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\EntityReferenceAutocompleteWidget;
use Drupal\Core\Form\FormStateInterface;

/**
 * Override original functionality to handle taxonomy term reference.
 *
 * @FieldWidget(
 *   id = "cp_entity_reference_autocomplete",
 *   label = @Translation("CP Autocomplete"),
 *   description = @Translation("CP An autocomplete text field."),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class CpEntityReferenceAutocompleteWidget extends EntityReferenceAutocompleteWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    return parent::formElement($items, $delta, $element, $form, $form_state);
    $entity = $items->getEntity();
    $form_state_storage = $form_state->getStorage();
    /** @var \Drupal\taxonomy\Entity\Vocabulary $vocabulary */
    $vocabulary = $form_state_storage['taxonomy_terms_widget_vocabulary'];
    $referenced_entities = $items->referencedEntities();

    // Append the match operation to the selection settings.
    $selection_settings = $this->getFieldSetting('handler_settings') + ['match_operator' => $this->getSetting('match_operator')];

    $element += [
      '#type' => 'entity_autocomplete',
      '#target_type' => $this->getFieldSetting('target_type'),
      '#selection_handler' => $this->getFieldSetting('handler'),
      '#selection_settings' => $selection_settings,
      // Entity reference field items are handling validation themselves via
      // the 'ValidReference' constraint.
      '#validate_reference' => FALSE,
      '#maxlength' => 1024,
      '#default_value' => isset($referenced_entities[$delta]) ? $referenced_entities[$delta] : NULL,
      '#size' => $this->getSetting('size'),
      '#placeholder' => $this->getSetting('placeholder'),
    ];

    $reference_items = $items;
    $this->removeUnrelatedItems($reference_items, $vocabulary->id());
    $field_state = static::getWidgetState([], $this->fieldDefinition->getName(), $form_state);
    $field_state['items_count'] = $reference_items->count();
    static::setWidgetState([], $this->fieldDefinition->getName(), $form_state, $field_state);

    return ['target_id' => $element];
  }

  /**
   *
   */
  public static function addMoreSubmit(array $form, FormStateInterface $form_state) {
    // parent::addMoreSubmit($form, $form_state);return;.
    $button = $form_state->getTriggeringElement();

    // Go two level up in the form, to the widgets container.
    $element = NestedArray::getValue($form, array_slice($button['#array_parents'], 0, -2));
    $field_name = $element['#field_name'];
    $parents = $element['#field_parents'];

    // Increment the items count.
    $field_state = static::getWidgetState($parents, $field_name, $form_state);
    $field_state['items_count']++;
    static::setWidgetState($parents, $field_name, $form_state, $field_state);

    $form_state->setRebuild();
  }

}
