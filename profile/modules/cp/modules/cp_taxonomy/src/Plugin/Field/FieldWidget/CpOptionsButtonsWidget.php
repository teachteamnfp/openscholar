<?php

namespace Drupal\cp_taxonomy\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\taxonomy\Entity\Vocabulary;

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
    $form_state_storage = $form_state->getStorage();
    /** @var \Drupal\taxonomy\Entity\Vocabulary $vocabulary */
    $vocabulary = $form_state_storage['taxonomy_terms_widget_vocabulary'];
    $options = $this->getHierarchyOptions($vocabulary);

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

  /**
   * Get options with hyphen hierarchy.
   *
   * @param \Drupal\taxonomy\Entity\Vocabulary $vocabulary
   *   Current vocabulary.
   *
   * @return array
   *   Options array.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  protected function getHierarchyOptions(Vocabulary $vocabulary): array {
    /** @var \Drupal\taxonomy\Plugin\EntityReferenceSelection\TermSelection $term_selection_plugin */
    $term_selection_plugin = $this->selectionPluginManager->createInstance('default:taxonomy_term');
    $config = $term_selection_plugin->getConfiguration();
    $config['target_bundles'] = [
      $vocabulary->id() => $vocabulary->id(),
    ];
    $config['target_type'] = 'taxonomy_term';
    $term_selection_plugin->setConfiguration($config);
    $options = $term_selection_plugin->getReferenceableEntities();
    $options = ['_none' => $this->t('- None -')] + $options;
    array_walk_recursive($options, [$this, 'sanitizeLabel']);

    return $options;
  }

}
