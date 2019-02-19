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
    $media_select_list = $block_content->get('field_media_select');
    $referenced_entities = $media_select_list->referencedEntities();

    foreach ($referenced_entities as $delta => $media) {
      $bundle = $media->bundle();
      switch ($bundle) {
        case 'image':
          $field_values = $media->get('field_media_image')->getValue();
          $file_id = $field_values[0]['target_id'];
          $file = File::load($file_id);
          $uri = $file->getFileUri();
          $embed_media = [
            '#theme' => 'image',
            '#uri' => $uri,
            '#alt' => $field_values[0]['alt'],
            '#title' => $field_values[0]['title'],
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
