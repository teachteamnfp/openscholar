<?php

namespace Drupal\cp_taxonomy\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'cp_options_buttons' widget.
 *
 * @FieldWidget(
 *   id = "cp_options_buttons",
 *   label = @Translation("CP Check boxes/radio buttons"),
 *   field_types = {
 *     "entity_reference",
 *   },
 *   multiple_values = TRUE
 * )
 */
class CpOptionsButtonsWidget extends CpOptionsWidgetBase {

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
        '#type' => 'checkboxes',
        '#options' => $options[$vocabulary->id()],
        '#default_value' => $this->getSelectedOptions($items),
        '#title' => $vocabulary->label(),
      ];
    }
    return [];
  }

}
