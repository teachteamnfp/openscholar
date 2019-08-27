<?php

namespace Drupal\cp_taxonomy\Plugin\Field\FieldWidget;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Entity\EntityReferenceSelection\SelectionPluginManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Field\WidgetInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\Element;
use Drupal\cp_taxonomy\CpTaxonomyHelperInterface;
use Drupal\taxonomy\Entity\Vocabulary;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * Taxonomy terms widget for reference terms.
 *
 * @FieldWidget(
 *   id = "reference_taxonomy_terms",
 *   label = @Translation("Taxonomy terms widget"),
 *   field_types = {
 *     "entity_reference"
 *   },
 *   multiple_values = TRUE
 * )
 */
class TaxonomyTermsWidget extends WidgetBase implements WidgetInterface, ContainerFactoryPluginInterface {

  const WIDGET_TYPE_AUTOCOMPLETE = 'cp_entity_reference_autocomplete';
  const WIDGET_TYPE_OPTIONS_SELECT = 'cp_options_select';
  const WIDGET_TYPE_OPTIONS_BUTTONS = 'cp_options_buttons';
  const WIDGET_TYPE_TREE = 'term_reference_tree';

  /**
   * Cp taxonomy helper.
   *
   * @var \Drupal\cp_taxonomy\CpTaxonomyHelperInterface
   */
  protected $taxonomyHelper;

  /**
   * Instance of selected widget.
   *
   * @var \Drupal\Core\Field\WidgetInterface
   */
  protected $fieldWidgets;
  protected $widgetTypes;
  protected $pluginManager;
  protected $entityTypeManager;
  protected $selectionPluginManager;

  /**
   * TaxonomyTermsWidget constructor.
   *
   * @param string $plugin_id
   *   The plugin_id for the widget.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the widget is associated.
   * @param array $settings
   *   The widget settings.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\cp_taxonomy\CpTaxonomyHelperInterface $taxonomy_helper
   *   Config Factory.
   * @param \Drupal\Component\Plugin\PluginManagerInterface $plugin_manager
   *   Plugin manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager.
   * @param \Drupal\Core\Entity\EntityReferenceSelection\SelectionPluginManagerInterface $selection_plugin_manager
   *   Selection Plugin Manager.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, CpTaxonomyHelperInterface $taxonomy_helper, PluginManagerInterface $plugin_manager, EntityTypeManagerInterface $entity_type_manager, SelectionPluginManagerInterface $selection_plugin_manager) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    $this->taxonomyHelper = $taxonomy_helper;
    $this->widgetTypes = $this->taxonomyHelper->getWidgetTypes($field_definition->getTargetEntityTypeId() . ':' . $field_definition->getTargetBundle());
    $this->pluginManager = $plugin_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->selectionPluginManager = $selection_plugin_manager;

    $configuration['field_definition'] = $field_definition;
    $configuration['settings'] = $settings;
    $configuration['third_party_settings'] = $third_party_settings;
    foreach ($this->widgetTypes as $vid => $widgetInfo) {
      $this->fieldWidgets[$vid] = $this->pluginManager->createInstance($widgetInfo['widget_type'], $configuration);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('cp.taxonomy.helper'),
      $container->get('plugin.manager.field.widget'),
      $container->get('entity_type.manager'),
      $container->get('plugin.manager.entity_reference_selection')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $main_element = [
      '#tree' => TRUE,
    ];
    /** @var \Drupal\Core\Field\Plugin\Field\FieldWidget\OptionsWidgetBase $fieldWidget */
    foreach ($this->fieldWidgets as $vid => $fieldWidget) {
      $filtered_items = $this->removeUnrelatedItems($items, $vid);
      $vocabulary = $this->entityTypeManager->getStorage('taxonomy_vocabulary')->load($vid);
      $this->saveVocabularyToFormState($form_state, $vocabulary);
      if ($fieldWidget->handlesMultipleValues()) {
        $main_element[$vid] = $fieldWidget->formElement($filtered_items, $delta, $element, $form, $form_state);
      }
      else {
        if ($fieldWidget->getPluginId() == 'term_reference_tree') {
          $main_element[$vid]['#vocabularies'] = [
            $vid => $main_element[$vid]['#vocabularies'][$vid],
          ];
        }
        else {
          $field_name = $this->fieldDefinition->getName();
          $parents = $form['#parents'];
          $field_state = static::getWidgetState($parents, $field_name, $form_state);
          if (empty($field_state['original_deltas'])) {
            $field_state['items_count'] = $filtered_items->count();
          }
          static::setWidgetState($parents, $field_name, $form_state, $field_state);
          $entity_reference_autocomplete_elements = $fieldWidget->formMultipleElements($filtered_items, $form, $form_state);
          $entity_reference_autocomplete_elements['add_more']['#name'] .= '_vid_' . $vid;
          $entity_reference_autocomplete_elements['add_more']['#vocabulary_id'] = $vid;
          foreach (Element::children($entity_reference_autocomplete_elements) as $delta) {
            $element = &$entity_reference_autocomplete_elements[$delta];
            if (empty($element['target_id']['#selection_settings']['view']['arguments'][0])) {
              continue;
            }
            $element['target_id']['#selection_settings']['view']['arguments'][0] .= '|' . $vid;
          }
          $main_element[$vid] = $entity_reference_autocomplete_elements;
          $main_element[$vid]['#title'] = $vocabulary->label();
        }
      }
    }
    return $main_element;
  }

  /**
   * {@inheritdoc}
   */
  public function errorElement(array $element, ConstraintViolationInterface $error, array $form, FormStateInterface $form_state) {
    return $this->fieldWidgets->errorElement($element, $error, $form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    $merged_values = [];
    foreach ($this->fieldWidgets as $vid => $fieldWidget) {
      if (isset($values[$vid]['add_more'])) {
        unset($values[$vid]['add_more']);
      }
      $widget_values = $fieldWidget->massageFormValues($values[$vid], $form, $form_state);
      if (empty($widget_values)) {
        continue;
      }
      foreach ($widget_values as $value) {
        if (in_array($value, $merged_values) || empty($value)) {
          continue;
        }
        $merged_values[] = $value;
      }
    }
    return $merged_values;
  }

  /**
   * Get widget types to options.
   */
  public static function getWidgetTypes() {
    return [
      static::WIDGET_TYPE_AUTOCOMPLETE => t('Autocomplete'),
      static::WIDGET_TYPE_OPTIONS_SELECT => t('Select list'),
      static::WIDGET_TYPE_OPTIONS_BUTTONS => t('Check boxes / Radio buttons'),
      static::WIDGET_TYPE_TREE => t('Tree'),
    ];
  }

  /**
   * Remove term items which not related to current vid.
   *
   * @param \Drupal\Core\Field\FieldItemListInterface $items
   *   Array of default values for this field.
   * @param string $vid
   *   Vocabulary id.
   *
   * @return \Drupal\Core\Field\FieldItemListInterface
   *   Array of filtered values.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function removeUnrelatedItems(FieldItemListInterface $items, string $vid) {
    $filtered_items = clone $items;
    /** @var \Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem $item */
    foreach ($filtered_items as $item) {
      $delta = $item->getName();
      $field_value = $item->getValue();
      if (empty($field_value)) {
        continue;
      }
      $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($field_value['target_id']);
      if ($term->bundle() != $vid) {
        unset($filtered_items[$delta]);
      }
    }
    return $filtered_items;
  }

  /**
   * Save current vocabulary to form state.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   * @param \Drupal\taxonomy\Entity\Vocabulary $vocabulary
   *   Current vocabulary entity.
   */
  protected function saveVocabularyToFormState(FormStateInterface $form_state, Vocabulary $vocabulary): void {
    $form_state_storage = $form_state->getStorage();
    $form_state_storage['taxonomy_terms_widget_vocabulary'] = $vocabulary;
    $form_state->setStorage($form_state_storage);
  }

}
