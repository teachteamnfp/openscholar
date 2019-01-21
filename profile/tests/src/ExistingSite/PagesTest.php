<?php

namespace Drupal\Tests\openscholar\ExistingSite;

/**
 * Tests book pages.
 *
 * @group openscholar
 * @group kernel
 */
class PagesTest extends TestBase {

  /**
   * Tests alias.
   */
  public function testAlias() {
    /** @var \Drupal\Core\Path\AliasManagerInterface $alias_manager */
    $alias_manager = $this->container->get('path.alias_manager');

    /** @var \Drupal\node\NodeInterface $book */
    $book = $this->createBookPage([
      'title' => 'First book',
    ]);

    $this->assertEquals($alias_manager->getAliasByPath("/node/{$book->id()}"), '/first-book');
  }

  /**
   * Tests book outline.
   */
  public function testOutline() {
    /** @var \Drupal\node\NodeInterface $book */
    $book = $this->createBookPage();
    $this->bookManager->updateOutline($book);

    /** @var \Drupal\node\NodeInterface $page1 */
    $page1 = $this->createBookPage([], $book->id());
    $this->bookManager->updateOutline($page1);

    /** @var \Drupal\node\NodeInterface $page11 */
    $page11 = $this->createBookPage([], $book->id(), $page1->id());
    $this->bookManager->updateOutline($page11);

    /** @var \Drupal\node\NodeInterface $page2 */
    $page2 = $this->createBookPage([], $book->id());
    $this->bookManager->updateOutline($page2);

    // Assert book has no parent and has correct number of children.
    $this->assertEquals(0, $book->book['pid']);
    $this->assertCount(2, $this->bookOutlineStorage->loadBookChildren($book->id()));

    // Assert page1 is placed correctly in the hierarchy.
    $this->assertEquals($book->id(), $page1->book['pid']);
    $this->assertEquals($book->id(), $page1->book['bid']);
    $this->assertCount(1, $this->bookOutlineStorage->loadBookChildren($page1->id()));

    // Assert page11 is placed correctly in the hierarchy.
    $this->assertEquals($page1->id(), $page11->book['pid']);
    $this->assertEquals($book->id(), $page11->book['bid']);
    $this->assertCount(0, $this->bookOutlineStorage->loadBookChildren($page11->id()));

    // Assert page2 is placed correctly in the hierarchy.
    $this->assertEquals($book->id(), $page2->book['pid']);
    $this->assertEquals($book->id(), $page2->book['bid']);
    $this->assertCount(0, $this->bookOutlineStorage->loadBookChildren($page2->id()));
  }

}
