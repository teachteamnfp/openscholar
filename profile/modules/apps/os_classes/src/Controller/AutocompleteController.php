<?php

namespace Drupal\os_classes\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\NodeInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Defines a route controller for entity autocomplete form elements.
 */
class AutocompleteController extends ControllerBase {

  /**
   * Handler for autocomplete request.
   */
  public function handleAutocompleteYearOffered(Request $request, $vsite) {
    $results = [];

    // Get the typed string from the URL, if it exists.
    if ($input = $request->query->get('q')) {
      $query = \Drupal::database()
        ->select('node__field_year_offered', 'fyo')
        ->fields('fyo', [
          'field_year_offered_value',
        ]);
      $query->distinct();
      $query->join('node_field_data', 'n', 'fyo.entity_id = n.nid');
      $query->join('group_content_field_data', 'gfd', 'fyo.entity_id = gfd.entity_id');
      $query->condition('gfd.type', 'personal-group_node-class')
        ->condition('n.status', NodeInterface::PUBLISHED)
        ->condition('gfd.gid', $vsite)
        ->condition('fyo.field_year_offered_value', $input . '%', 'LIKE')
        ->orderBy('fyo.field_year_offered_value', 'ASC')
        ->range(0, 10);
      $result = $query->execute();
      while ($row = $result->fetchAssoc()) {
        $results[] = $row['field_year_offered_value'];
      }
    }

    return new JsonResponse($results);
  }

}
