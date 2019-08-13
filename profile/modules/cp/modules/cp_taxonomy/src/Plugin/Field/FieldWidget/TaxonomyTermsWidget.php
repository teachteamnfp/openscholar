<?php

namespace Drupal\cp_taxonomy\Plugin\Field\FieldWidget;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Field\WidgetInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
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
    foreach ($this->fieldWidgets as $vid => $fieldWidget) {
      $main_element['#field_widget_definitions'][$vid] = $fieldWidget->formElement($items, $delta, $element, $form, $form_state);
      $main_element['#field_widget_definitions'][$vid] += [
        '#chosen' => 1,
      ];
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
    foreach ($this->widgetTypes as $vid => $widgetInfo) {
      $widget_values = $this->fieldWidgets[$vid]->massageFormValues($values[$vid], $form, $form_state);
      if (empty($widget_values)) {
        continue;
      }
      foreach ($widget_values as $value) {
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

}
