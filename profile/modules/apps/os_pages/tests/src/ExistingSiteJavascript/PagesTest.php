<?php

namespace Drupal\Tests\os_pages\ExistingSiteJavascript;

use Behat\Mink\Exception\ExpectationException;

/**
 * Tests book pages.
 *
 * @group openscholar
 * @group functional-javascript
 */
class PagesTest extends TestBase {

  /**
   * Tests visibility of book outline.
   */
  public function testBookVisibility() {
    /** @var \Drupal\book\BookManagerInterface $book_manager */
    $book_manager = $this->container->get('book.manager');
    /** @var \Drupal\Core\Path\AliasManagerInterface $path_alias_manager */
    $path_alias_manager = $this->container->get('path.alias_manager');

    /** @var \Drupal\node\NodeInterface $book1 */
    $book1 = $this->createBookPage([
      'title' => 'Harry Potter and the Philosophers Stone',
    ]);
    $book_manager->updateOutline($book1);

    /** @var \Drupal\node\NodeInterface $page1 */
    $page1 = $this->createBookPage([
      'title' => 'The Boy Who Lived',
    ], $book1->id());
    $book_manager->updateOutline($page1);

    /** @var \Drupal\node\NodeInterface $book2 */
    $book2 = $this->createBookPage([
      'title' => 'Harry Potter and the Deathly Hallows',
    ]);
    $book_manager->updateOutline($book2);

    /** @var \Drupal\node\NodeInterface $event */
    $event = $this->createNode([
      'type' => 'events',
    ]);

    $web_assert = $this->assertSession();

    try {
      $this->visit($path_alias_manager->getAliasByPath("/node/{$book1->id()}"));

      $this->assertNotNull($web_assert->elementExists('css', '.block-book-navigation'));
      $web_assert->pageTextContains($book1->label());
      $web_assert->pageTextContains($page1->label());
      $web_assert->pageTextContains($book2->label());

      $this->visit($path_alias_manager->getAliasByPath("/node/{$event->id()}"));
      $web_assert->elementNotExists('css', '.block-book-navigation');
      $web_assert->pageTextNotContains($book1->label());
      $web_assert->pageTextNotContains($book2->label());

      $this->assertTrue(TRUE);
    }
    catch (ExpectationException $e) {
      $this->fail(sprintf("Test failed: %s\nBacktrace: %s", $e->getMessage(), $e->getTraceAsString()));
    }
  }

}
