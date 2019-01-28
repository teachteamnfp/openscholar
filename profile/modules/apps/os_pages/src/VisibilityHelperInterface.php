<?php

namespace Drupal\os_pages;

use Drupal\Core\Entity\EntityInterface;

/**
 * Helper service for block visibility group.
 */
interface VisibilityHelperInterface {

  /**
   * Checks whether the page is first page of a book.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The newly created page.
   *
   * @return bool
   *   TRUE if yes, otherwise FALSE.
   */
  public function isBookFirstPage(EntityInterface $entity) : bool;

}
