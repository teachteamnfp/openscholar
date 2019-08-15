<?php

namespace Drupal\cp_taxonomy\Plugin\Field\FieldWidget;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldFilteredMarkup;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Field\WidgetInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\OptGroup;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\Element;
use Drupal\cp_taxonomy\CpTaxonomyHelperInterface;
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

  const WIDGET_TYPE_AUTOCOMPLETE = 'entity_reference_autocomplete';
  const WIDGET_TYPE_OPTIONS_SELECT = 'options_select';
  const WIDGET_TYPE_OPTIONS_BUTTONS = 'options_buttons';
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
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, CpTaxonomyHelperInterface $taxonomy_helper, PluginManagerInterface $plugin_manager) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    $this->taxonomyHelper = $taxonomy_helper;
    $this->widgetTypes = $this->taxonomyHelper->getWidgetTypes($field_definition->getTargetEntityTypeId() . ':' . $field_definition->getTargetBundle());
    $this->pluginManager = $plugin_manager;

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
      $container->get('plugin.manager.field.widget')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $main_element = [];
    /** @var \Drupal\Core\Field\Plugin\Field\FieldWidget\OptionsWidgetBase $fieldWidget */
    foreach ($this->fieldWidgets as $vid => $fieldWidget) {
      $plugin_id = $fieldWidget->getPluginId();
      if (in_array($plugin_id, ['options_select', 'options_buttons'])) {
        $options = $this->getOptions($items->getEntity());
        if (!empty($options[$vid])) {
          $main_element['#field_widget_definitions'][$vid] = [
            '#type' => $plugin_id == 'options_select' ? 'select' : 'checkboxes',
            '#options' => $options[$vid],
            '#default_value' => $this->getSelectedOptions($items),
            '#multiple' => 1,
            '#chosen' => 1,
            '#title' => $vid,
          ];
        }
      }
      else {
        $main_element['#field_widget_definitions'][$vid] = $fieldWidget->formElement($items, $delta, $element, $form, $form_state);
        if ($plugin_id == 'term_reference_tree') {
          $main_element['#field_widget_definitions'][$vid]['#vocabularies'] = [
            $vid => $main_element['#field_widget_definitions'][$vid]['#vocabularies'][$vid],
          ];
          $main_element['#field_widget_definitions'][$vid]['#title'] = $vid;
        }
        if ($plugin_id == 'entity_reference_autocomplete') {
          $entity_reference_autocomplete_elements = $fieldWidget->formMultipleElements($items, $form, $form_state);
          foreach (Element::children($entity_reference_autocomplete_elements) as $delta) {
            $element = &$entity_reference_autocomplete_elements[$delta];
            if (empty($element['target_id']['#selection_settings']['view']['arguments'][0])) {
              continue;
            }
            $element['target_id']['#selection_settings']['view']['arguments'][0] .= '|' . $vid;
          }
          $main_element['#field_widget_definitions'][$vid]['#entity_reference_autocomplete_elements'] = $entity_reference_autocomplete_elements;
          $main_element['#field_widget_definitions'][$vid]['#entity_reference_autocomplete_elements']['#title'] = $vid;
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
   * Returns the array of options for the widget.
   *
   * @param \Drupal\Core\Entity\FieldableEntityInterface $entity
   *   The entity for which to return options.
   *
   * @return array
   *   The array of options for the widget.
   */
  protected function getOptions(FieldableEntityInterface $entity) {
    if (!isset($this->options)) {
      $options_provider = $this->fieldDefinition
        ->getFieldStorageDefinition()
        ->getOptionsProvider('target_id', $entity);
      $field_definition = $options_provider->getFieldDefinition();
      $options = \Drupal::service('plugin.manager.entity_reference_selection')->getSelectionHandler($field_definition, $entity)->getReferenceableEntities();

      $options = ['_none' => t('- None -')] + $options;

      $module_handler = \Drupal::moduleHandler();
      $context = [
        'fieldDefinition' => $this->fieldDefinition,
        'entity' => $entity,
      ];
      $module_handler->alter('options_list', $options, $context);

      array_walk_recursive($options, [$this, 'sanitizeLabel']);

      $this->options = $options;
    }
    return $this->options;
  }

  /**
   * Determines selected options from the incoming field values.
   *
   * @param \Drupal\Core\Field\FieldItemListInterface $items
   *   The field values.
   *
   * @return array
   *   The array of corresponding selected options.
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

  /**
   * Sanitizes a string label to display as an option.
   *
   * @param string $label
   *   The label to sanitize.
   */
  protected function sanitizeLabel(&$label) {
    // Allow a limited set of HTML tags.
    $label = FieldFilteredMarkup::create($label);
  }

}
