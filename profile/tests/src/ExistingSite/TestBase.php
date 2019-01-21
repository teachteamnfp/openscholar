<?php

namespace Drupal\Tests\openscholar\ExistingSite;

use Drupal\node\NodeInterface;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Test base for running profile tests.
 */
abstract class TestBase extends ExistingSiteBase {

  /**
   * Creates a book page.
   *
   * @param array $values
   *   Initial values.
   * @param int|null $book_id
   *   (Optional) Book id where this page should be attached. If nothing is
   *   passed, this creates a new book.
   *
   * @return \Drupal\node\NodeInterface
   *   The new entity.
   */
  public function createBookPage(array $values, $book_id = NULL) : NodeInterface {
    /** @var \Drupal\node\NodeInterface $book */
    $book = $this->createNode($values + [
      'type' => 'page',
      'book' => [
        'bid' => $book_id ?: 'new',
      ],
    ]);

    return $book;
  }

}
