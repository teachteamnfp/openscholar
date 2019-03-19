<?php

namespace Drupal\os_widgets;

use Drupal\Core\Entity\EntityInterface;
use Drupal\block_content\BlockContentViewBuilder;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;

/**
 * View builder handler for OS custom blocks.
 */
class OsBlockContentViewBuilder extends BlockContentViewBuilder {

  /**
   * Specific per-entity building.
   *
   * @param array $build
   *   The render array that is being created.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to be prepared.
   * @param \Drupal\Core\Entity\Display\EntityViewDisplayInterface $display
   *   The entity view display holding the display options configured for the
   *   entity components.
   * @param string $view_mode
   *   The view mode that should be used to prepare the entity.
   */
  protected function alterBuild(array &$build, EntityInterface $entity, EntityViewDisplayInterface $display, $view_mode) {

    $plugin_id = $entity->bundle() . '_widget';

    $type = \Drupal::service('plugin.manager.os_widgets');
    if (!$type->getDefinition($plugin_id, FALSE)) {
      return;
    }

    $plugin = $type->createInstance($plugin_id);
    if (!($plugin instanceof OsWidgetsInterface)) {
      return;
    }

    $plugin->buildBlock($build, $build['#block_content']);

  }

  /**
   * {@inheritdoc}
   *
   * @todo Should we add vsite ID here?
   */
  public function getCacheTags() {
    $tags = parent::getCacheTags();

    // $tags[] = bundle . '_view';.
    return $tags;
  }

}
