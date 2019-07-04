<?php

namespace Drupal\cp_taxonomy\Plugin\Field\FieldWidget;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Component\Utility\Html;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\OptionsSelectWidget;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Field\WidgetInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\OptGroup;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
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
 *   }
 * )
 */
class TaxonomyTermsWidget extends WidgetBase implements WidgetInterface, ContainerFactoryPluginInterface {

  const WIDGET_TYPE_AUTOCOMPLETE = 'entity_reference_autocomplete';
  const WIDGET_TYPE_OPTIONS_SELECT = 'options_select';
  const WIDGET_TYPE_OPTIONS_BUTTONS = 'options_buttons';
  const WIDGET_TYPE_TREE = 'term_reference_tree';

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;
  protected $column;
  protected $widgetType;
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
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory.
   * @param \Drupal\Component\Plugin\PluginManagerInterface $plugin_manager
   *   Plugin manager.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, ConfigFactoryInterface $config_factory, PluginManagerInterface $plugin_manager) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    $this->configFactory = $config_factory;
    $property_names = $this->fieldDefinition->getFieldStorageDefinition()->getPropertyNames();
    $this->column = $property_names[0];
    $config = $this->configFactory->get('cp_taxonomy.settings');
    $this->widgetType = $config->get('widget_type');
    $this->pluginManager = $plugin_manager;
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
      $container->get('config.factory'),
      $container->get('plugin.manager.field.widget')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    // $field_widget = $this->pluginManager->createInstance($this->widgetType);.
    switch ($this->widgetType) {
      case self::WIDGET_TYPE_AUTOCOMPLETE:
        $element = $this->getAutocompleteElement($element, $items, $delta);
        break;

      case self::WIDGET_TYPE_OPTIONS_SELECT:
        $element = $this->getOptionsSelectElement($element, $items);
        break;

      case self::WIDGET_TYPE_OPTIONS_BUTTONS:
        $element = $this->getOptionsButtonsElement($element, $items);
        break;
    }

    return $element;
  }

  /**
   * Get element for Autocomplete widget.
   */
  protected function getAutocompleteElement($element, FieldItemListInterface $items, $delta) {
    $referenced_entities = $items->referencedEntities();
    // Append the match operation to the selection settings.
    $selection_settings = $this->getFieldSetting('handler_settings') + ['match_operator' => 'CONTAINS'];

    $element += [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'taxonomy_term',
      '#selection_handler' => 'views',
      '#selection_settings' => $selection_settings,
      // Entity reference field items are handling validation themselves via
      // the 'ValidReference' constraint.
      '#validate_reference' => FALSE,
      '#maxlength' => 1024,
      '#default_value' => isset($referenced_entities[$delta]) ? $referenced_entities[$delta] : NULL,
      '#size' => 60,
    ];

    return ['target_id' => $element];
  }

  /**
   * Get element for OptionsSelect widget.
   */
  protected function getOptionsSelectElement($element, FieldItemListInterface $items) {
    $element += [
      '#title' => $this->t('Tags with terms'),
      '#type' => 'select',
      '#key_column' => 'target_id',
      '#element_validate' => [
        [
          OptionsSelectWidget::class,
          'validateElement',
        ],
      ],
      '#options' => $this->getOptions($items->getEntity()),
      '#multiple' => TRUE,
      '#default_value' => $this->getSelectedOptions($items),
    ];

    return $element;
  }

  /**
   * Get element for OptionsButtons widget.
   */
  protected function getOptionsButtonsElement($element, FieldItemListInterface $items) {
    $element += [
      '#title' => $this->t('Tags with terms'),
      '#type' => 'checkboxes',
      '#key_column' => 'target_id',
      '#element_validate' => [
        [
          OptionsSelectWidget::class,
          'validateElement',
        ],
      ],
      '#options' => $this->getOptions($items->getEntity()),
      '#multiple' => TRUE,
      '#default_value' => $this->getSelectedOptions($items),
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function errorElement(array $element, ConstraintViolationInterface $error, array $form, FormStateInterface $form_state) {
    return $element['target_id'];
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    foreach ($values as $key => $value) {
      // The entity_autocomplete form element returns an array when an entity
      // was "autocreated", so we need to move it up a level.
      if (is_array($value['target_id'])) {
        unset($values[$key]['target_id']);
        $values[$key] += $value['target_id'];
      }
    }

    return $values;
  }

  /**
   * Get widget types to options.
   */
  public static function getWidgetTypes() {
    return [
      self::WIDGET_TYPE_AUTOCOMPLETE => t('Autocomplete'),
      self::WIDGET_TYPE_OPTIONS_SELECT => t('Select list'),
      self::WIDGET_TYPE_OPTIONS_BUTTONS => t('Check boxes / Radio buttons'),
      self::WIDGET_TYPE_TREE => t('Tree'),
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
      // Limit the settable options for the current user account.
      $options = $this->fieldDefinition
        ->getFieldStorageDefinition()
        ->getOptionsProvider($this->column, $entity)
        ->getSettableOptions(\Drupal::currentUser());

      // Add an empty option if the widget needs one.
      if ($empty_label = $this->getEmptyLabel()) {
        $options = ['_none' => $empty_label] + $options;
      }

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
      $value = $item->{$this->column};
      // Keep the value if it actually is in the list of options (needs to be
      // checked against the flat list).
      if (isset($flat_options[$value])) {
        $selected_options[] = $value;
      }
    }

    return $selected_options;
  }

  /**
   * Returns the empty option label to add to the list of options, if any.
   *
   * @return string|null
   *   Either a label of the empty option, or NULL.
   */
  protected function getEmptyLabel() {
    if ($this->widgetType == self::WIDGET_TYPE_OPTIONS_SELECT) {
      return t('- None -');
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginDefinition() {
    $definition = parent::getPluginDefinition();
    if (in_array($this->widgetType, [self::WIDGET_TYPE_OPTIONS_SELECT, self::WIDGET_TYPE_OPTIONS_BUTTONS])) {
      $definition['multiple_values'] = TRUE;
    }
    return $definition;
  }

  /**
   * {@inheritdoc}
   */
  protected function sanitizeLabel(&$label) {
    // Select form inputs allow unencoded HTML entities, but no HTML tags.
    $label = Html::decodeEntities(strip_tags($label));
  }

}
