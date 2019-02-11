<?php

namespace Drupal\os_media\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\media\Entity\MediaType;

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
    $settings = $this->getFieldSettings();
    $bundles = $settings['handler_settings']['target_bundles'];
    $extensions = '';
    $types = [];
    /** @var MediaType[] $mediaTypes */
    $mediaTypes = \Drupal::entityTypeManager()->getStorage('media_type')->loadMultiple($bundles);
    foreach ($mediaTypes as $type) {
      $types[] = $type->id();
    }

    $element['#type'] = 'fieldset';
    $element['media-browser-field'] = [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#attributes' => [
        'media-browser-field' => '',
        'types' => implode(',',$types),
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
