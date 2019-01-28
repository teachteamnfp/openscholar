<?php

namespace Drupal\os_pages;

use Drupal\Core\Entity\EntityInterface;

/**
 * Helper service for block visibility group.
 */
interface VisibilityHelperInterface {

  /**
   * Checks whether block visibility group for page should be created.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity which is taken into account.
   *
   * @return bool
   *   TRUE if it is okay, otherwise FALSE.
   */
  public function shouldCreatePageVisibilityGroup(EntityInterface $entity): bool;

  /**
   * Checks whether block visibility group for section should be created.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity which is taken into account.
   *
   * @return bool
   *   TRUE if it is okay, otherwise FALSE.
   */
  public function shouldCreateSectionVisibilityGroup(EntityInterface $entity) : bool;

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
