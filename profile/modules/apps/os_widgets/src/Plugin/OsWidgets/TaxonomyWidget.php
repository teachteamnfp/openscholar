<?php

namespace Drupal\os_widgets\Plugin\OsWidgets;

use Drupal\block_content\Entity\BlockContent;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\os_widgets\OsWidgetsBase;
use Drupal\os_widgets\OsWidgetsInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class TaxonomyWidget.
 *
 * @OsWidget(
 *   id = "taxonomy_widget",
 *   title = @Translation("Taxonomy")
 * )
 */
class TaxonomyWidget extends OsWidgetsBase implements OsWidgetsInterface {

  protected $requestStack;

  /**
   * {@inheritdoc}
   */
  public function __construct($configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, Connection $connection, RequestStack $request_stack) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager, $connection);
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('database'),
      $container->get('request_stack')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildBlock(&$build, $block_content) {
    $field_taxonomy_vocabulary_values = $block_content->get('field_taxonomy_vocabulary')->getValue();
    $field_taxonomy_tree_depth_values = $block_content->get('field_taxonomy_tree_depth')->getValue();
    $field_taxonomy_show_children_values = $block_content->get('field_taxonomy_show_children')->getValue();
    $vid = $field_taxonomy_vocabulary_values[0]['target_id'];
    $depth = empty($field_taxonomy_tree_depth_values[0]['value']) ? NULL : $field_taxonomy_tree_depth_values[0]['value'];
    // When unchecked, only show top level terms.
    if (empty($field_taxonomy_show_children_values[0]['value'])) {
      $depth = 1;
    }
    $settings['vid'] = $vid;
    $settings['depth'] = $depth;
    $settings['bundles'] = $this->getFilteredBundles($block_content);
    $terms = $this->getTerms($settings);
    $term_items = [];
    foreach ($terms as $term) {
      $term_items[] = [
        '#theme' => 'os_widgets_taxonomy_term_item',
        '#term' => $term,
        '#label' => str_repeat('-', $term->depth) . $term->name,
      ];
    }
    $build['taxonomy']['terms'] = [
      '#theme' => 'item_list',
      '#items' => $term_items,
    ];
  }

  /**
   * Collect terms by field values.
   */
  private function getTerms($settings) {
    $terms = $this->entityTypeManager->getStorage('taxonomy_term')->loadTree($settings['vid'], 0, $settings['depth']);
    return $terms;
  }

  /**
   * Collect bundles in array depend on other field selection.
   *
   * @param \Drupal\block_content\Entity\BlockContent $block_content
   *   Block Content entity.
   *
   * @return array
   *   Collected bundles array.
   */
  protected function getFilteredBundles(BlockContent $block_content): array {
    $field_taxonomy_behavior_values = $block_content->get('field_taxonomy_behavior')->getValue();
    $bundles = [];
    if ($field_taxonomy_behavior_values[0]['value'] == 'select') {
      $field_taxonomy_bundles_values = $block_content->get('field_taxonomy_bundles')
        ->getValue();
      foreach ($field_taxonomy_bundles_values as $field_taxonomy_bundles_value) {
        $bundles[] = $field_taxonomy_bundles_value['value'];
      }
    }
    if ($field_taxonomy_behavior_values[0]['value'] == 'contextual') {
      if ($node = $this->requestStack->getCurrentRequest()->attributes->get('node')) {
        $bundles[] = $node->bundle();
      }
    }
    return $bundles;
  }

}
