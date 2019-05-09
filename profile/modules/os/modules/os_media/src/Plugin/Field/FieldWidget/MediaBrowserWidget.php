<?php

namespace Drupal\os_media\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\os\AngularModuleManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class MediaBrowserWidget.
 *
 * @FieldWidget(
 *   id = "media_browser_widget",
 *   label = @Translation("Media Browser"),
 *   field_types = {
 *     "entity_reference"
 *   },
 *   multiple_values = TRUE,
 * )
 */
class MediaBrowserWidget extends WidgetBase implements ContainerFactoryPluginInterface {

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Angular module manager.
   *
   * @var \Drupal\os\AngularModuleManagerInterface
   */
  protected $angularModuleManager;

  /**
   * MediaBrowserWidget constructor.
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
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   * @param \Drupal\os\AngularModuleManagerInterface $angular_module_manager
   *   Angular module manager.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, EntityTypeManagerInterface $entity_type_manager, AngularModuleManagerInterface $angular_module_manager) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    $this->entityTypeManager = $entity_type_manager;
    $this->angularModuleManager = $angular_module_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($plugin_id, $plugin_definition, $configuration['field_definition'], $configuration['settings'], $configuration['third_party_settings'], $container->get('entity_type.manager'), $container->get('angular.module_manager'));
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $media = [];
    for ($i = 0, $l = $items->count(); $i < $l; $i++) {
      $media[] = $items->get($i);
    }
    $settings = $this->getFieldSettings();
    $bundles = $settings['handler_settings']['target_bundles'];
    $types = [];
    /** @var \Drupal\media\Entity\MediaType[] $mediaTypes */
    $mediaTypes = $this->entityTypeManager->getStorage('media_type')->loadMultiple($bundles);
    foreach ($mediaTypes as $type) {
      $types[] = $type->id();
    }

    $element['#type'] = 'fieldset';
    $element['media-browser-field'] = [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#attributes' => [
        'media-browser-field' => '',
        'types' => implode(',', $types),
        'max-filesize' => '512 MB',
        'upload_text' => 'Upload',
        'droppable_text' => 'Drop here.',
        'cardinality' => $this->fieldDefinition->getFieldStorageDefinition()->getCardinality(),
        'files' => 'files',
      ],
      '#markup' => $this->t('Loading the Media Browser. Please wait a moment.'),
      '#attached' => [
        'library' => [
          'os_media/mediaBrowserField',
        ],
        'drupalSettings' => [
          'mediaBrowserField' => [
            Html::cleanCssIdentifier('edit-' . $this->fieldDefinition->getName()) => [
              'selectedFiles' => $media,
            ],
          ],
        ],
      ],
      '#post_render' => [
        [$this, 'addNgModule'],
      ],
    ];

    return $element;
  }

  /**
   * Adds the AngularJS module to the page.
   */
  public function addNgModule() {
    $this->angularModuleManager->addModule('MediaBrowserField');
  }

}
