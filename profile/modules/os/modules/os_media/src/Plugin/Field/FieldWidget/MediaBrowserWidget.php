<?php

namespace Drupal\os_media\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class MediaBrowserWidget
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
class MediaBrowserWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $media = $items;

    $element['#type'] = 'fieldset';
    $element['media-browser-field'] = [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#attributes' => [
        'media-browser-field' => '',
        'types' => 'all',
        'maxFilesize' => '512 MB',
        'upload_text' => 'Upload',
        'droppable_text' => 'Drop here.',
        'cardinality' => -1,
        'files' => 'files'
      ],
      '#markup' => t('Loading the Media Browser. Please wait a moment.'),
      '#attached' => [
        'library' => [
          'os_media/mediaBrowserField'
        ],
        'drupalSettings' => [
          'mediaBrowserField' => [
            Html::cleanCssIdentifier('edit-'.$this->fieldDefinition->getName()) => [
              'selectedFiles' => $media
            ]
          ]
        ]
      ],
      '#post_render' => [
        array($this, 'addNgModule')
      ]
    ];

    return $element;
  }

  /**
   * Adds the AngularJS module to the page.
   */
  public function addNgModule() {
    /** @var \Drupal\os\AngularModuleManagerInterface $moduleManager */
    $moduleManager = \Drupal::service('angular.module_manager');
    $moduleManager->addModule('MediaBrowserField');
  }

}
