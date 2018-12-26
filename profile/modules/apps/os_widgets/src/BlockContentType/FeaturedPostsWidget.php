<?php

namespace Drupal\os_widgets\BlockContentType;


class FeaturedPostsWidget implements BlockContentTypeInterface {

  /**
   * @param $variables
   *
   * @param $blockContent
   *
   * @return mixed
   */
  function buildBlock($variables, $blockContent) {
    if (empty($blockContent)) {
      return $variables;
    }
    $displayStyleValues = $blockContent->get('field_display_style')->getValue();
    $displayStyle = $displayStyleValues[0]['value'];
    $viewBuilder = \Drupal::entityTypeManager()->getViewBuilder('node');
    /** @var  $fieldItemsList \Drupal\Core\Field\EntityReferenceFieldItemList */
    $fieldItemsList = $variables['content']['field_featured_posts']['#items'];
    if (empty($fieldItemsList)) {
      return $variables;
    }
    $referencedEntities = $fieldItemsList->referencedEntities();
    $hideTitleValues = $blockContent->get('field_hide_title')->getValue();
    if ($displayStyle != 'title') {
      /**
       * @var  $delta int
       * @var  $node \Drupal\node\Entity\Node
       */
      foreach ($referencedEntities as $delta => $node) {
        $build = $viewBuilder->view($node, $displayStyle);
        $build['os_widgets_hide_node_title'] = !empty($hideTitleValues[0]['value']) ? true : false;
        $variables['content']['field_featured_posts'][$delta] = $build;
      }
    }
    $isRandomValues = $blockContent->get('field_is_random')->getValue();
    if (!empty($isRandomValues[0]['value'])) {
      $displayedDelta = array_rand($referencedEntities);
      foreach ($referencedEntities as $delta => $node) {
        if ($displayedDelta != $delta) {
          // Hide other referenced entity
          $variables['content']['field_featured_posts'][$delta]['#access'] = false;
        }
      }
    }
    $isStyledValues = $blockContent->get('field_is_styled')->getValue();
    if (!empty($isStyledValues[0]['value'])) {
      $variables['attributes']['class'][] = 'styled';
    }
    return $variables;
  }
}
