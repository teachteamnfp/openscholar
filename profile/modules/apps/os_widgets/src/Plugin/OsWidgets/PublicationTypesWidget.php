<?php

namespace Drupal\os_widgets\Plugin\OsWidgets;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Url;
use Drupal\os_widgets\OsWidgetsBase;
use Drupal\os_widgets\OsWidgetsInterface;
use Drupal\vsite\Plugin\VsiteContextManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class PublicationTypesWidget.
 *
 * @OsWidget(
 *   id = "publication_types_widget",
 *   title = @Translation("Publication types")
 * )
 */
class PublicationTypesWidget extends OsWidgetsBase implements OsWidgetsInterface {

  /**
   * Vsite context manager.
   *
   * @var \Drupal\vsite\Plugin\VsiteContextManagerInterface
   */
  private $vsiteContextManager;

  /**
   * {@inheritdoc}
   */
  public function __construct($configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, Connection $connection, VsiteContextManagerInterface $vsite_context_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager, $connection);
    $this->vsiteContextManager = $vsite_context_manager;
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
      $container->get('vsite.context_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildBlock(&$build, $block_content) {
    $group = $this->vsiteContextManager->getActiveVsite();
    // Collect all reference types with original allowed values function.
    /** @var \Drupal\field\Entity\FieldConfig $field_types_whitelist_definition */
    $field_types_whitelist_definition = $block_content->getFieldDefinition('field_types_whitelist');
    $allowed_values_function = $field_types_whitelist_definition->getSetting('allowed_values_function');
    $allowed_values = [];
    if (function_exists($allowed_values_function)) {
      $allowed_values = $allowed_values_function();
    }

    // Fill an array with whitelisted reference types and create links.
    // Init count with default zero.
    $field_types_whitelist_values = $block_content->get('field_types_whitelist')->getValue();
    $types_list = [];
    $types_count_list = [];
    foreach ($field_types_whitelist_values as $field_types_whitelist_value) {
      $type_machine_name = $field_types_whitelist_value['value'];
      $types_list[] = $type_machine_name;
      $types_count_list[$type_machine_name] = [
        'label' => $allowed_values[$type_machine_name] ?? $this->t('NoLabel'),
        'href' => Url::fromRoute('view.publications.page_1', ['type' => $type_machine_name])->toString(),
        'count' => '0',
      ];
    }

    // Collect count of references with related reference types.
    $query = $this->connection->select('bibcite_reference', 'br');
    $query->fields('br', ['type']);
    $query->condition('br.type', $types_list, 'IN');
    // Filter to vsite content.
    if ($group) {
      $ids = [];
      $publications = $group->getContentEntities('group_entity:bibcite_reference');
      foreach ($publications as $publication) {
        $ids[] = $publication->id();
      }
      if (!empty($ids)) {
        $query->condition('br.id', $ids, 'IN');
      }
    }
    $query->addExpression('COUNT(br.id)', 'count');
    $query->groupBy('br.type');
    $result = $query->execute();
    while ($row = $result->fetchAssoc()) {
      $types_count_list[$row['type']]['count'] = $row['count'];
    }

    $field_display_count_values = $block_content->get('field_display_count')->getValue();
    $build['types_list'] = [
      '#theme' => 'os_widgets_publication_types',
      '#types' => $types_count_list,
      '#is_display_count' => !empty($field_display_count_values[0]['value']),
    ];
  }

}
