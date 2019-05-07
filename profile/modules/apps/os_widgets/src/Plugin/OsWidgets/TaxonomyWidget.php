<?php

namespace Drupal\os_widgets\Plugin\OsWidgets;

use DateTime;
use DateTimeZone;
use Drupal\block_content\Entity\BlockContent;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Database;
use Drupal\Core\Database\Query\Condition;
use Drupal\Core\Database\Query\SelectInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Link;
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

  /**
   * Request stack that controls the lifecycle of requests.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Store data to handle get terms.
   *
   * @var array
   */
  protected $settings;

  /**
   * Selected theme function.
   *
   * @var string
   */
  protected $themeFunction;

  /**
   * Time interface for obtaining system time.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * Cp Taxonomy Helper.
   *
   * @var \Drupal\cp_taxonomy\CpTaxonomyHelperInterface
   */
  protected $taxonomyHelper;

  /**
   * {@inheritdoc}
   */
  public function __construct($configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, Connection $connection, RequestStack $request_stack, CpTaxonomyHelperInterface $taxonomy_helper, TimeInterface $time) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager, $connection);
    $this->requestStack = $request_stack;
    $this->taxonomyHelper = $taxonomy_helper;
    $this->time = $time;
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
      $container->get('cp.taxonomy.helper'),
      $container->get('datetime.time')
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
    $this->settings['vid'] = $vid;
    $this->settings['depth'] = $depth;
    $this->settings['bundles'] = $this->getFilteredBundles($block_content, $vid);
    $this->settings['show_empty_terms'] = !empty($field_taxonomy_show_empty_terms_values[0]['value']);
    $terms = $this->getTerms();
    switch ($field_taxonomy_display_type_values[0]['value']) {
      case 'menu':
        $this->themeFunction = 'os_widgets_taxonomy_display_type_menu';
        break;

      case 'slider':
        $this->themeFunction = 'os_widgets_taxonomy_display_type_slider';
        break;

      default:
        $this->themeFunction = 'item_list';
        break;
    }
    $term_items = $this->getRenderableTerms($block_content, $terms);
    $build['taxonomy']['terms'] = [
      '#theme' => $this->themeFunction,
      '#items' => $term_items,
    ];
  }

  /**
   * Collect terms by field values.
   *
   * @return array
   *   List of filtered terms.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function getTerms() {
    $terms = $this->entityTypeManager->getStorage('taxonomy_term')->loadTree($this->settings['vid'], 0, $this->settings['depth']);
    if (empty($terms) || empty($this->settings['bundles'])) {
      return $terms;
    }
    $terms_count = $this->getTermsCount($terms);

    $keep_term_tids = [];
    // Only show_empty_terms is FALSE case, we need to check parent visibility.
    if (!$this->settings['show_empty_terms']) {
      // Mark tids to handle what term can be deleted.
      foreach ($terms_count as $tid => $count) {
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
      if (!$this->settings['show_empty_terms'] && !in_array($term->tid, $keep_term_tids)) {
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
    $field_taxonomy_show_count_values = $block_content->get('field_taxonomy_show_count')->getValue();
    $term_items = [];
    $tree = [];
    foreach ($terms as $term) {
      $description = '';
      if (!empty($field_taxonomy_show_term_desc_values[0]['value'])) {
        $description = check_markup($term->description__value, $term->description__format);
      }
      $label = $term->name;
      if (!empty($field_taxonomy_show_count_values[0]['value']) && !empty($term->entity_reference_count)) {
        $label .= ' (' . $term->entity_reference_count . ')';
      }
      $label = Link::createFromRoute($label, 'entity.taxonomy_term.canonical', ['taxonomy_term' => $term->tid]);
      $term_items[$term->tid] = [
        '#theme' => 'os_widgets_taxonomy_term_item',
        '#term' => $term,
        '#label' => $label,
        '#description' => $description,
      ];
      $tree[$term->parents[0]][] = $term->tid;
    }
    if (empty($tree[0])) {
      return $term_items;
    }
    $root_items = $tree[0];
    $offset_value = $field_taxonomy_offset_values[0]['value'] ?? 0;
    $range_value = $field_taxonomy_range_values[0]['value'] ?? 0;
    if ($range_value) {
      $root_items = array_slice($root_items, $offset_value, $range_value);
    }
    $renderable_terms = [];
    foreach ($root_items as $tid) {
      $renderable_terms[] = $this->renderTerm($tid, $tree, $term_items);
    }

    return $renderable_terms;
  }

  /**
   * Recursive function to handle render term and children.
   *
   * @param int $tid
   *   Current term id.
   * @param array $tree
   *   Array of all parent/child relation tree.
   * @param array $term_items
   *   Key is tid and built array for taxonomy.
   *
   * @return array
   *   Array of built term.
   */
  private function renderTerm(int $tid, array $tree, array $term_items) {
    $build = $term_items[$tid];
    if (empty($tree[$tid])) {
      return $build;
    }
    foreach ($tree[$tid] as $child_tid) {
      $build['#children']['#theme'] = $this->themeFunction;
      $build['#children']['#items'][] = $this->renderTerm($child_tid, $tree, $term_items);
    }
    return $build;
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
   *
   * @throws \Exception
   */
  protected function buildCountQuery(array $tids, string $entity_name, array $bundles): SelectInterface {
    $query = Database::getConnection()->select('taxonomy_term_data', 'td');
    $query->fields('td', ['tid']);
    $query->condition('td.tid', $tids, 'IN');
    $alias = $entity_name . '_ftt';
    $query->leftJoin($entity_name . '__field_taxonomy_terms', $alias, $alias . '.field_taxonomy_terms_target_id = td.tid');
    $special_event_types = [];
    foreach ($bundles as $i => $bundle) {
      if (in_array($bundle, ['past_events', 'upcoming_events'])) {
        $special_event_types[$bundle] = $bundle;
        unset($bundles[$i]);
      }
      // Fix bundles array if remove everything.
      if (count($bundles) == 0) {
        $bundles[] = 'events';
      }
    }
    // If both special type is present, then nothing to do.
    // If only one selected, then we need to filter.
    if (count($special_event_types) == 1) {
      $special_type = reset($special_event_types);
      $query->leftJoin('node__field_recurring_date', 'frd', $alias . '.entity_id = frd.entity_id');
      $db_or = new Condition('OR');
      $db_or->isNull('frd.field_recurring_date_value');
      $request_time = $this->time->getRequestTime();
      $new_datetime = new DateTime();
      $new_datetime->setTimestamp($request_time);
      $new_datetime->setTimezone(new DateTimeZone('GMT'));
      if ($special_type == 'past_events') {
        $date = $new_datetime->format("Y-m-d");
        $db_or->condition('frd.field_recurring_date_value', $date, '<');
      }
      if ($special_type == 'upcoming_events') {
        // Set current time before 30 minutes.
        $new_datetime->setTimestamp($request_time - 30 * 60);
        $date = $new_datetime->format("Y-m-d\TH:i:s");
        $db_or->condition('frd.field_recurring_date_value', $date, '>=');
      }
      $query->condition($db_or);
    }
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

  /**
   * Collect all related entities count.
   *
   * @param array $terms
   *   Loaded loadTree() terms array of objects.
   *
   * @return array
   *   Key tid and value count array.
   */
  protected function getTermsCount(array $terms): array {
    $tids = [];
    foreach ($terms as $term) {
      $tids[] = $term->tid;
    }
    $entities = $this->taxonomyHelper->explodeEntityBundles($this->settings['bundles']);
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
    return $terms_count;
  }

}
