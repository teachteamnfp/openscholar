<?php

namespace Drupal\cp_taxonomy\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'cp_options_select' widget.
 *
 * @FieldWidget(
 *   id = "cp_options_select",
 *   label = @Translation("CP Select list"),
 *   field_types = {
 *     "entity_reference",
 *   },
 *   multiple_values = TRUE
 * )
 */
class CpOptionsSelectWidget extends CpOptionsWidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $options = $this->getOptions($items->getEntity());
    $form_state_storage = $form_state->getStorage();
    /** @var \Drupal\taxonomy\Entity\Vocabulary $vocabulary */
    $vocabulary = $form_state_storage['taxonomy_terms_widget_vocabulary'];
    if (!empty($options[$vocabulary->id()])) {
      return [
        '#type' => 'select',
        '#options' => $options[$vocabulary->id()],
        '#default_value' => $this->getSelectedOptions($items),
        '#multiple' => 1,
        '#chosen' => 1,
        '#title' => $vocabulary->label(),
      ];
    }
    return [];
  }

}
