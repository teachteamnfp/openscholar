<?php

namespace Drupal\cp_taxonomy\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\term_reference_tree\Plugin\Field\FieldWidget\TermReferenceTree;

/**
 * Plugin implementation of the 'cp_term_reference_tree' widget.
 *
 * @FieldWidget(
 *   id = "cp_term_reference_tree",
 *   label = @Translation("CP Term reference tree"),
 *   field_types = {"entity_reference"},
 *   multiple_values = TRUE
 * )
 */
class CpTermReferenceTree extends TermReferenceTree {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $form_state_storage = $form_state->getStorage();
    /** @var \Drupal\taxonomy\Entity\Vocabulary $vocabulary */
    $vocabulary = $form_state_storage['taxonomy_terms_widget_vocabulary'];
    $vocabularies = Vocabulary::loadMultiple([$vocabulary->id()]);

    $element['#type'] = 'checkbox_tree';
    $element['#default_value'] = $items->getValue();
    $element['#vocabularies'] = $vocabularies;
    $element['#max_choices'] = $this->fieldDefinition->getFieldStorageDefinition()
      ->getCardinality();
    $element['#leaves_only'] = $this->getSetting('leaves_only');
    $element['#select_parents'] = $this->getSetting('select_parents');
    $element['#cascading_selection'] = $this->getSetting('cascading_selection');
    $element['#value_key'] = 'target_id';
    $element['#max_depth'] = $this->getSetting('max_depth');
    $element['#start_minimized'] = $this->getSetting('start_minimized');
    $element['#element_validate'] = [
      [
        get_class($this),
        'validateTermReferenceTreeElement',
      ],
    ];
    return $element;
  }

}
