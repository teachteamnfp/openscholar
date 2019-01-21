<?php

namespace Drupal\Tests\openscholar\ExistingSite;

use Drupal\node\NodeInterface;
use Drupal\Tests\book\Functional\BookTestTrait;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Test base for running profile tests.
 */
abstract class TestBase extends ExistingSiteBase {

  use BookTestTrait;

  /**
   * The book outline storage.
   *
   * @var \Drupal\book\BookOutlineStorageInterface
   */
  protected $bookOutlineStorage;

  /**
   * The book manager.
   *
   * @var \Drupal\book\BookManagerInterface
   */
  protected $bookManager;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->bookOutlineStorage = $this->container->get('book.outline_storage');
    $this->bookManager = $this->container->get('book.manager');
  }

  /**
   * Creates a book page.
   *
   * @param array $values
   *   (Optional) Initial values. If nothing is passed, the page is created with
   *   default values.
   * @param int|null $book_id
   *   (Optional) Book id where this page should be attached. If nothing is
   *   passed, this creates a new book.
   * @param int|null $parent_id
   *   (Optional) The parent id. If nothing is passed, the page will become the
   *   first page.
   *
   * @return \Drupal\node\NodeInterface
   *   The new entity.
   */
  public function createBookPage(array $values = [], $book_id = NULL, $parent_id = NULL) : NodeInterface {
    /** @var \Drupal\node\NodeInterface $book */
    $book = $this->createNode($values + [
      'type' => 'page',
      'book' => [
        'bid' => $book_id ?? 'new',
        'pid' => $parent_id ?? -1,
        'weight' => 0,
      ],
    ]);

    return $book;
  }

}
