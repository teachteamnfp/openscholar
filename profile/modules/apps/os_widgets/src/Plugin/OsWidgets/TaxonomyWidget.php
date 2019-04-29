<?php

namespace Drupal\os_widgets\Plugin\OsWidgets;

use Drupal\block_content\Entity\BlockContent;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Database;
use Drupal\Core\Database\Query\SelectInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\cp_taxonomy\CpTaxonomyHelperInterface;
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
   * Cp Taxonomy Helper.
   *
   * @var \Drupal\cp_taxonomy\CpTaxonomyHelperInterface
   */
  protected $taxonomyHelper;

  /**
   * {@inheritdoc}
   */
  public function __construct($configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, Connection $connection, RequestStack $request_stack, CpTaxonomyHelperInterface $taxonomy_helper) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager, $connection);
    $this->requestStack = $request_stack;
    $this->taxonomyHelper = $taxonomy_helper;
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
      $container->get('request_stack'),
      $container->get('cp.taxonomy.helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildBlock(&$build, $block_content) {
    $field_taxonomy_vocabulary_values = $block_content->get('field_taxonomy_vocabulary')->getValue();
    $field_taxonomy_tree_depth_values = $block_content->get('field_taxonomy_tree_depth')->getValue();
    $field_taxonomy_show_children_values = $block_content->get('field_taxonomy_show_children')->getValue();
    $field_taxonomy_display_type_values = $block_content->get('field_taxonomy_display_type')->getValue();
    $field_taxonomy_show_empty_terms_values = $block_content->get('field_taxonomy_show_empty_terms')->getValue();
    $vid = $field_taxonomy_vocabulary_values[0]['target_id'];
    $depth = empty($field_taxonomy_tree_depth_values[0]['value']) ? NULL : $field_taxonomy_tree_depth_values[0]['value'];
    // When unchecked, only show top level terms.
    if (empty($field_taxonomy_show_children_values[0]['value'])) {
      $depth = 1;
    }
    $settings['vid'] = $vid;
    $settings['depth'] = $depth;
    $settings['bundles'] = $this->getFilteredBundles($block_content, $vid);
    $settings['show_empty_terms'] = !empty($field_taxonomy_show_empty_terms_values[0]['value']);
    $terms = $this->getTerms($settings);
    $term_items = $this->getRenderableTerms($block_content, $terms);
    switch ($field_taxonomy_display_type_values[0]['value']) {
      case 'menu':
        $theme = 'os_widgets_taxonomy_display_type_menu';
        break;

      case 'slider':
        $theme = 'os_widgets_taxonomy_display_type_slider';
        break;

      default:
        $theme = 'item_list';
        break;
    }
    $build['taxonomy']['terms'] = [
      '#theme' => $theme,
      '#items' => $term_items,
    ];
  }

  /**
   * Collect terms by field values.
   *
   * @param array $settings
   *   Parameters to select terms.
   *
   * @return array
   *   List of filtered terms.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function getTerms(array $settings) {
    $terms = $this->entityTypeManager->getStorage('taxonomy_term')->loadTree($settings['vid'], 0, $settings['depth']);
    if (empty($terms) || empty($settings['bundles'])) {
      return $terms;
    }
    $tids = [];
    foreach ($terms as $term) {
      $tids[] = $term->tid;
    }
    $entities = $this->taxonomyHelper->explodeEntityBundles($settings['bundles']);
    $terms_count = [];
    foreach ($entities as $entity_name => $bundles) {
      $query = $this->buildCountQuery($tids, $entity_name, $bundles);
      $result = $query->execute();
      while ($row = $result->fetchAssoc()) {
        if (isset($terms_count[$row['tid']])) {
          $terms_count[$row['tid']] += $row['count'];
        }
        else {
          $terms_count[$row['tid']] = $row['count'] ?? 0;
        }
      }
    }

    $keep_term_tids = [];
    // Only show_empty_terms is FALSE case, we need to check parent visibility.
    if (!$settings['show_empty_terms']) {
      // Mark tids to handle what term can be deleted.
      foreach ($terms_count as $tid => $count) {
        // If we set to hide empty terms and count is zero, don't keep in tree.
        if (!$settings['show_empty_terms'] && $count == 0) {
          continue;
        }
        // Get all parents include current one.
        $parents = $this->entityTypeManager->getStorage("taxonomy_term")->loadAllParents($tid);
        if (!empty($parents)) {
          foreach ($parents as $parent) {
            // Store current tid and all parent tids.
            $keep_term_tids[$parent->id()] = $parent->id();
          }
        }
      }
    }

    foreach ($terms as $i => $term) {
      // If show_empty_terms is TRUE, we don't unset any items.
      if (!$settings['show_empty_terms'] && !in_array($term->tid, $keep_term_tids)) {
        unset($terms[$i]);
        continue;
      }
      $terms[$i]->entity_reference_count = $terms_count[$term->tid] ?? 0;
    }
    return $terms;
  }

  /**
   * Collect bundles in array depend on other field selection.
   *
   * @param \Drupal\block_content\Entity\BlockContent $block_content
   *   Block Content entity.
   * @param string $vid
   *   Vocabulary id.
   *
   * @return array
   *   Collected bundles array.
   */
  protected function getFilteredBundles(BlockContent $block_content, string $vid): array {
    $field_taxonomy_behavior_values = $block_content->get('field_taxonomy_behavior')->getValue();
    $bundles = [];
    switch ($field_taxonomy_behavior_values[0]['value']) {
      case 'select':
        if (!empty($block_content->get('field_taxonomy_bundle')->value)) {
          $bundles[] = $block_content->get('field_taxonomy_bundle')->value;
        }
        break;

      case 'contextual':
        if ($node = $this->requestStack->getCurrentRequest()->attributes->get('node')) {
          $bundles[] = 'node:' . $node->bundle();
        }
        break;

      case '--all--':
        $data['vid']['#default_value'] = $vid;
        $bundles = $this->taxonomyHelper->getSelectedBundles($data);
        break;
    }
    return $bundles;
  }

  /**
   * Create a renderable array from terms entities.
   *
   * @param \Drupal\block_content\Entity\BlockContent $block_content
   *   Block content entity.
   * @param array $terms
   *   Filtered terms array.
   *
   * @return array
   *   Renderable list array.
   */
  protected function getRenderableTerms(BlockContent $block_content, array $terms): array {
    $field_taxonomy_show_term_desc_values = $block_content->get('field_taxonomy_show_term_desc')->getValue();
    $field_taxonomy_range_values = $block_content->get('field_taxonomy_range')->getValue();
    $field_taxonomy_offset_values = $block_content->get('field_taxonomy_offset')->getValue();
    $term_items = [];
    $count = 0;
    $offset = 0;
    $skip_child_terms = FALSE;
    foreach ($terms as $term) {
      // Offset only top level.
      if (!empty($field_taxonomy_offset_values[0]['value']) && $offset < $field_taxonomy_offset_values[0]['value']) {
        // If we are on top level, count.
        if ($term->depth == 0) {
          $offset++;
        }
        // If we reached offset, mark variable and handle rest of children.
        if ($offset == $field_taxonomy_offset_values[0]['value']) {
          $skip_child_terms = TRUE;
        }
        continue;
      }
      // When offset is reached,
      // then we skip all children what are under last term.
      if ($skip_child_terms && $term->depth > 0) {
        continue;
      }
      else {
        $skip_child_terms = FALSE;
      }
      // We must count only top level terms.
      if (!empty($field_taxonomy_range_values[0]['value']) && $term->depth == 0) {
        $count++;
        if ($count > $field_taxonomy_range_values[0]['value']) {
          break;
        }
      }
      $description = '';
      if (!empty($field_taxonomy_show_term_desc_values[0]['value'])) {
        $description = check_markup($term->description__value, $term->description__format);
      }
      $term_items[] = [
        '#theme' => 'os_widgets_taxonomy_term_item',
        '#term' => $term,
        '#label' => str_repeat('-', $term->depth) . $term->name,
        '#description' => $description,
      ];
    }
    return $term_items;
  }

  /**
   * Build count query.
   *
   * @param array $tids
   *   List of all vocabulary tids.
   * @param string $entity_name
   *   Entity name.
   * @param array $bundles
   *   Vocabulary allowed bundles.
   *
   * @return \Drupal\Core\Database\Query\SelectInterface
   *   Select Interface.
   */
  protected function buildCountQuery(array $tids, string $entity_name, array $bundles): SelectInterface {
    $query = Database::getConnection()->select('taxonomy_term_data', 'td');
    $query->fields('td', ['tid']);
    $query->condition('td.tid', $tids, 'IN');
    $alias = $entity_name . '_ftt';
    $query->leftJoin($entity_name . '__field_taxonomy_terms', $alias, $alias . '.field_taxonomy_terms_target_id = td.tid');
    $query->condition($alias . '.bundle', $bundles, 'IN');
    $field_data_id = '';
    switch ($entity_name) {
      case 'node':
        $field_data_id = 'nid';
        break;

      case 'media':
        $field_data_id = 'mid';
        break;

      case 'bibcite_reference':
        $query->leftJoin($entity_name, $entity_name, $entity_name . '.id' . ' = ' . $alias . '.entity_id');
        $query->condition($entity_name . '.status', 1);
        $query->addExpression('COUNT(id)', 'count');
        break;
    }
    if (!empty($field_data_id)) {
      $field_data_alias = $entity_name . 'fd';
      $query->leftJoin($entity_name . '_field_data', $field_data_alias, $field_data_alias . '.' . $field_data_id . ' = ' . $alias . '.entity_id');
      $query->condition($field_data_alias . '.status', 1);
      $query->addExpression('COUNT(' . $field_data_alias . '.' . $field_data_id . ')', 'count');
    }
    $query->groupBy('td.tid');
    return $query;
  }

}
