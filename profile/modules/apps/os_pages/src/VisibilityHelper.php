<?php

namespace Drupal\os_pages;

use Drupal\book\BookOutlineStorageInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\node\Entity\Node;

/**
 * Class PagesVisibilityHelper.
 */
final class VisibilityHelper implements VisibilityHelperInterface {

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
   * {@inheritdoc}
   */
  public function shouldCreatePageVisibilityGroup(EntityInterface $entity) : bool {
    return ($entity->getEntityType()->id() === 'node' &&
      $entity->bundle() === 'page');
  }

  /**
   * {@inheritdoc}
   */
  public function shouldCreateSectionVisibilityGroup(EntityInterface $entity) : bool {
    return ($this->shouldCreatePageVisibilityGroup($entity) &&
      $this->isBookFirstPage($entity));
  }

  /**
   * {@inheritdoc}
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
