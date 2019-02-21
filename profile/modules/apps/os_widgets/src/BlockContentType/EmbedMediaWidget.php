<?php

namespace Drupal\os_widgets\BlockContentType;

use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class EmbedMediaWidget.
 */
class EmbedMediaWidget implements BlockContentTypeInterface {

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   *   EntityTypeManagerInterface.
   */
  protected $entityTypeManager;

  /**
   * Class constructor.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager = NULL) {
    $this->entityTypeManager = is_null($entity_type_manager) ? \Drupal::entityTypeManager() : $entity_type_manager;
  }

  /**
   * Set Entity Type Manager.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   New entity type manager.
   */
  public function setEntityTypeManager(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

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
          $variables['content']['embed_media'][$delta] = $embed_media;
          break;

        case 'video_embed':
          $view_builder = $this->entityTypeManager->getViewBuilder('media');
          $embed_media = $view_builder->view($media, 'default');
          $variables['content']['embed_media'][$delta] = $embed_media;
          break;
      }
    }
    return $variables;
  }

}
