<?php

namespace Drupal\os_widgets\Plugin\OsWidgets;

use Drupal\os_widgets\OsWidgetsBase;
use Drupal\os_widgets\OsWidgetsInterface;

/**
 * Class TaxonomyWidget.
 *
 * @OsWidget(
 *   id = "taxonomy_widget",
 *   title = @Translation("Taxonomy")
 * )
 */
class TaxonomyWidget extends OsWidgetsBase implements OsWidgetsInterface {

  /**
   * {@inheritdoc}
   */
  public function buildBlock(&$build, $block_content) {
    $field_taxonomy_vocabulary_values = $block_content->get('field_taxonomy_vocabulary')->getValue();
    $vid = $field_taxonomy_vocabulary_values[0]['target_id'];
    $terms = $this->entityTypeManager->getStorage('taxonomy_term')->loadTree($vid);
    $term_data = [];
    foreach ($terms as $term) {
      $term_data[] = $term->name;
    }
    $build['taxonomy']['terms'] = [
      '#theme' => 'item_list',
      '#items' => $term_data,
    ];
  }

}
