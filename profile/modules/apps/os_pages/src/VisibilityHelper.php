<?php

namespace Drupal\os_pages;

use Drupal\book\BookOutlineStorageInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\node\Entity\Node;

/**
 * Class PagesVisibilityHelper.
 */
class VisibilityHelper {

  /**
   * The book outline storage.
   *
   * @var \Drupal\book\BookOutlineStorageInterface
   */
  protected $bookOutlineStorage;

  /**
   * VisibilityHelper constructor.
   *
   * @param \Drupal\book\BookOutlineStorageInterface $book_outline_storage
   *   The book outline storage.
   */
  public function __construct(BookOutlineStorageInterface $book_outline_storage) {
    $this->bookOutlineStorage = $book_outline_storage;
  }

  /**
   * Checks whether block visibility group for page should be created.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity which is taken into account.
   *
   * @return bool
   *   TRUE if it is okay, otherwise FALSE.
   */
  public function shouldCreatePageVisibilityGroup(EntityInterface $entity) : bool {
    return ($entity->getEntityType()->id() === 'node' &&
      $entity->bundle() === 'page');
  }

  /**
   * Checks whether block visibility group for section should be created.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity which is taken into account.
   *
   * @return bool
   *   TRUE if it is okay, otherwise FALSE.
   */
  public function shouldCreateSectionVisibilityGroup(EntityInterface $entity) : bool {
    return ($this->shouldCreatePageVisibilityGroup($entity) &&
      $this->isBookFirstPage($entity));
  }

  /**
   * Checks whether the page is first page of a book.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The newly created page.
   *
   * @return bool
   *   TRUE if yes, otherwise FALSE.
   */
  public function isBookFirstPage(EntityInterface $entity) : bool {
    if (empty($entity->book['pid']) || empty($entity->book['bid'])) {
      return FALSE;
    }

    // Book's first page is not considered, if, the page is not the immediate
    // child of the book.
    if ($entity->book['bid'] != $entity->book['pid']) {
      return FALSE;
    }

    /** @var \Drupal\node\NodeInterface $book */
    $book = Node::load($entity->book['bid']);

    /** @var array $book_pages */
    $book_pages = $this->bookOutlineStorage->loadBookChildren($book->id());

    return (count($book_pages) === 1);
  }

}
