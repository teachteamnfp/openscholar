<?php

namespace Drupal\os_widgets\Helper;

/**
 * RssHelperInterface.
 */
interface RssHelperInterface {

  /**
   * Created ordered array from original feed view.
   *
   * @return array
   *   Ordered multidimensional array.
   */
  public function getOriginalViewOrder(): array;

  /**
   * Created ordered array from reference feed view.
   *
   * @return array
   *   Ordered multidimensional array.
   */
  public function getReferenceViewOrder(): array;

  /**
   * Merge two ordered view result.
   *
   * @param array $order1
   *   Order array 1.
   * @param array $order2
   *   Order array 2.
   */
  public function mergeRows(array $order1, array $order2);

}
