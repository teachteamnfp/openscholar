<?php

namespace Drupal\os_widgets\Plugin\OsWidgets;

use Drupal\os_widgets\OsWidgetsBase;
use Drupal\os_widgets\OsWidgetsInterface;

/**
 * Class FeaturedPostsWidget.
 *
 * @OsWidget(
 *   id = "featured_posts_widget",
 *   title = @Translation("Featured Posts")
 * )
 */
class FeaturedPostsWidget extends OsWidgetsBase implements OsWidgetsInterface {

  /**
   * {@inheritdoc}
   */
  public function buildBlock(&$build, $block_content) {
    $displayStyleValues = $block_content->get('field_display_style')->getValue();
    $displayStyle = $displayStyleValues[0]['value'];
    $view_builder = $this->entityTypeManager->getViewBuilder('node');
    /** @var \Drupal\Core\Field\FieldItemList $media_select_list */
    $featured_posts_list = $block_content->get('field_featured_posts');
    if (empty($featured_posts_list)) {
      return;
    }
    $referenced_entities = $featured_posts_list->referencedEntities();
    $hide_title_values = $block_content->get('field_hide_title')->getValue();
    if ($displayStyle != 'title') {
      /** @var \Drupal\node\Entity\Node $node */
      foreach ($referenced_entities as $delta => $node) {
        $build_referenced_entity = $view_builder->view($node, $displayStyle);
        $build_referenced_entity['os_widgets_hide_node_title'] = !empty($hide_title_values[0]['value']) ? TRUE : FALSE;
        $build['field_featured_posts'][$delta] = $build_referenced_entity;
      }
    }
    $is_random_values = $block_content->get('field_is_random')->getValue();
    if (!empty($is_random_values[0]['value'])) {
      $displayedDelta = $this->shortRandom($referenced_entities);
      foreach ($referenced_entities as $delta => $node) {
        if ($displayedDelta != $delta) {
          // Hide other referenced entity.
          $build['field_featured_posts'][$delta]['#access'] = FALSE;
        }
      }
    }
    $is_styled_values = $block_content->get('field_is_styled')->getValue();
    if (!empty($is_styled_values[0]['value'])) {
      $build['#extra_classes'][] = 'styled';
    }
  }

  /**
   * Create a custom function, to easy to mock.
   */
  public function shortRandom(array $array) {
    return array_rand($array);
  }

}
