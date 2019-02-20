<?php

namespace Drupal\os_widgets\BlockContentType;

use Drupal\file\Entity\File;

/**
 * Class EmbedMediaWidget.
 */
class EmbedMediaWidget implements BlockContentTypeInterface {

  /**
   * {@inheritdoc}
   */
  public function buildBlock($variables, $block_content) {
    if (empty($block_content)) {
      return $variables;
    }
    $field_max_width_values = $block_content->get('field_max_width')->getValue();
    $max_width = $field_max_width_values[0]['value'] ?? 0;
    /** @var \Drupal\Core\Field\FieldItemList $media_select_list */
    $media_select_list = $block_content->get('field_media_select');
    $referenced_entities = $media_select_list->referencedEntities();

    foreach ($referenced_entities as $delta => $media) {
      $bundle = $media->bundle();
      switch ($bundle) {
        case 'image':
          $field_media_image = $media->get('field_media_image');
          $referenced_files = $field_media_image->referencedEntities();
          /** @var \Drupal\file\Entity\File $file */
          $file = $referenced_files[0];
          $uri = $file->getFileUri();
          $field_media_image_values = $field_media_image->getValue();
          $embed_media = [
            '#theme' => 'image',
            '#uri' => $uri,
            '#alt' => $field_media_image_values[0]['alt'],
            '#title' => $field_media_image_values[0]['title'],
            '#width' => $max_width,
          ];
          $variables['content']['embed_media'] = $embed_media;
          break;

        case 'video':
          $field_values = $media->get('field_media_oembed_video');
          break;
      }
    }
    return $variables;
  }

}
