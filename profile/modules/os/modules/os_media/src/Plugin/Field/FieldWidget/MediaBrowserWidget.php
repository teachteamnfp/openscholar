<?php

namespace Drupal\os_media\Plugin\Field\FieldWidget;


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

    $element['#type'] = 'fieldset';
    $element['media-browser-field'] = [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#attributes' => [
        'media-browser-field' => '',
      ],
      '#markup' => t('Loading the Media Browser. Please wait a moment.'),
      '#attached' => [
        'library' => [
          'os_media/mediaBrowserField'
        ]
      ]
    ];

    return $element;
  }

}
