<?php

namespace Drupal\os_widgets\Helper;

use Drupal\views\ViewExecutable;

/**
 * Helper class for merging views with different entity types.
 */
class RssHelper implements RssHelperInterface {

  /**
   * Original base view.
   *
   * @var \Drupal\views\ViewExecutable
   *   ViewExecutable.
   */
  protected $originalView;
  /**
   * Mergable view (reference entity)
   *
   * @var \Drupal\views\ViewExecutable
   *   ViewExecutable
   */
  protected $referenceView;
  /**
   * Rendered array by hook_views_post_render.
   *
   * @var array
   *   Output array.
   */
  protected $output;

  /**
   * Set parameter.
   *
   * @param \Drupal\views\ViewExecutable $originalView
   *   ViewExecutable.
   */
  public function setOriginalView(ViewExecutable $originalView): void {
    $this->originalView = $originalView;
  }

  /**
   * Set parameter.
   *
   * @param \Drupal\views\ViewExecutable $referenceView
   *   ViewExecutable.
   */
  public function setReferenceView(ViewExecutable $referenceView): void {
    $this->referenceView = $referenceView;
  }

  /**
   * Set parameter.
   *
   * @param array $output
   *   Output array.
   */
  public function setOutput(array $output): void {
    $this->output = $output;
  }

  /**
   * Get built output.
   *
   * @return array
   *   Output array.
   */
  public function getOutput(): array {
    return $this->output;
  }

  /**
   * {@inheritdoc}
   */
  public function getOriginalViewOrder() {
    // Create an array to order by created (os_feeds).
    $original_view_order = [];
    if (!empty($this->originalView->result)) {
      foreach ($this->originalView->result as $index => $row) {
        $created = $row->_entity->getCreatedTime();
        // Handle same timestamp rows.
        $original_view_order[$created][] = $this->output['#rows'][$index];
      }
    }
    return $original_view_order;
  }

  /**
   * {@inheritdoc}
   */
  public function getReferenceViewOrder() {
    // Create an array to order by created (os_reference_feed).
    $reference_view_order = [];
    foreach ($this->referenceView->display_handler->output['#rows'] as $index => $row) {
      $created = $this->referenceView->result[$index]->_entity->getCreatedTime();
      // Handle same timestamp rows.
      $reference_view_order[$created][] = $row;
    }
    return $reference_view_order;
  }

  /**
   * {@inheritdoc}
   */
  public function mergeRows($original_view_order, $reference_view_order) {
    // Rebuild #rows array by merged created.
    $this->resetRows();
    list($original_created, $original_items) = $this->readItem($original_view_order);
    list($reference_created, $reference_items) = $this->readItem($reference_view_order);
    while (!empty($original_items) || !empty($reference_items)) {
      // Checking created date.
      if ($reference_created > $original_created || empty($original_items)) {
        $this->addSameCreatedItems($reference_items);
        list($reference_created, $reference_items) = $this->readItem($reference_view_order);
        continue;
      }
      $this->addSameCreatedItems($original_items);
      list($original_created, $original_items) = $this->readItem($original_view_order);
    }
  }

  /**
   * Reset output rows.
   */
  protected function resetRows() {
    $this->output['#rows'] = [];
  }

  /**
   * Read one item from timestamp keyed array.
   *
   * @param array $array
   *   Input array.
   *
   * @return array
   *   Key and read item.
   */
  protected function readItem(array &$array) {
    $key = key($array);
    $item = $array[$key];
    unset($array[$key]);
    return [$key, $item];
  }

  /**
   * Add multidimensional array to output rows.
   *
   * @param array $items
   *   Array input.
   */
  protected function addSameCreatedItems(array $items) {
    foreach ($items as $item) {
      if ($this->originalView->pager->getItemsPerPage() <= count($this->output['#rows'])) {
        continue;
      }
      $this->output['#rows'][] = $item;
    }
  }

}
