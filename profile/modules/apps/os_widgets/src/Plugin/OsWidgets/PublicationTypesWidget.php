<?php

namespace Drupal\os_widgets\Plugin\OsWidgets;

use Drupal\Core\Url;
use Drupal\os_widgets\OsWidgetsBase;
use Drupal\os_widgets\OsWidgetsInterface;

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
   * {@inheritdoc}
   */
  public function buildBlock(&$build, $block_content) {
    if (empty($block_content)) {
      return;
    }

    /** @var \Drupal\field\Entity\FieldConfig $field_types_whitelist_definition */
    $field_types_whitelist_definition = $block_content->getFieldDefinition('field_types_whitelist');
    $allowed_values_function = $field_types_whitelist_definition->getSetting('allowed_values_function');
    $allowed_values = [];
    if (function_exists($allowed_values_function)) {
      $allowed_values = $allowed_values_function();
    }
    $field_types_whitelist_values = $block_content->get('field_types_whitelist')->getValue();
    $types_list = [];
    $types_count_list = [];
    foreach ($field_types_whitelist_values as $field_types_whitelist_value) {
      $type_machine_name = $field_types_whitelist_value['value'];
      $types_list[] = $type_machine_name;
      $types_count_list[$type_machine_name] = [
        'label' => $allowed_values[$type_machine_name] ?? 'NoLabel',
        'href' => Url::fromRoute('view.publications.page_1', ['type' => $type_machine_name])->toString(),
        'count' => '0',
      ];
    }

    $query = $this->connection->select('bibcite_reference', 'br');
    $query->fields('br', ['type']);
    $query->condition('br.type', $types_list, 'IN');
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
      '#is_display_count' => !empty($field_display_count_values[0]['value']) ? TRUE : FALSE,
    ];
  }

}
