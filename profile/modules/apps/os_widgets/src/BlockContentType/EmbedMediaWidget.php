<?php

namespace Drupal\os_widgets\BlockContentType;

/**
 * Class FeaturedPostsWidget.
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
    $max_width = $field_max_width_values[0]['value'];
    $view_builder = \Drupal::entityTypeManager()->getViewBuilder('node');
    /** @var \Drupal\Core\Field\EntityReferenceFieldItemList $fieldItemsList */
    $media_select_list = $variables['content']['field_media_select']['#items'];
    $referenced_entities = $media_select_list->referencedEntities();

    foreach ($referenced_entities as $delta => $node) {
      // TODO: continue implement view layer.
    }
    return $variables;
  }

}
