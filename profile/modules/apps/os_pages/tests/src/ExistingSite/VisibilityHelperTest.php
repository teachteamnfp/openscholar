<?php

namespace Drupal\Tests\os_pages;

use Drupal\block_visibility_groups\Entity\BlockVisibilityGroup;
use Drupal\Tests\openscholar\ExistingSite\TestBase;

/**
 * VisibilityHelperTest.
 *
 * @group openscholar
 * @group kernel
 * @coversDefaultClass \Drupal\os_pages\VisibilityHelper
 */
class VisibilityHelperTest extends TestBase {

  /**
   * Tests if visibility group added when a new book is created.
   *
   * @covers ::shouldCreatePageVisibilityGroup
   * @covers ::isBookFirstPage
   */
  public function testCreateVisibilityGroupWhenBookCreated() {
    /** @var \Drupal\node\NodeInterface $book */
    $book = $this->createBookPage();

    $this->assertNull(BlockVisibilityGroup::load("os_pages_{$book->id()}"));
  }

  /**
   * Tests if visibility group added when a non-page node created.
   *
   * @covers ::shouldCreatePageVisibilityGroup
   * @covers ::isBookFirstPage
   */
  public function testCreateVisibilityGroupWhenNonPageNodeCreated() {
    /** @var \Drupal\node\NodeInterface $book */
    $book = $this->createBookPage();

    /** @var \Drupal\node\NodeInterface $event */
    $event = $this->createNode([
      'type' => 'event',
    ]);

    $this->assertNull(BlockVisibilityGroup::load("os_pages_{$book->id()}_{$event->id()}"));
  }

  /**
   * Tests if visibility group added when first sub-page node is created.
   *
   * @covers ::shouldCreatePageVisibilityGroup
   * @covers ::isBookFirstPage
   */
  public function testCreateVisibilityGroupWhenFirstSubPageNodeCreated() {
    /** @var \Drupal\node\NodeInterface $book */
    $book = $this->createBookPage();

    /** @var \Drupal\node\NodeInterface $first_sub_page */
    $first_sub_page = $this->createBookPage([], $book->id());

    $this->assertNotNull(BlockVisibilityGroup::load("os_pages_{$book->id()}_{$first_sub_page->id()}"));
  }

  /**
   * Tests if visibility group added when first sub-page's sub-page is created.
   *
   * @covers ::shouldCreatePageVisibilityGroup
   * @covers ::isBookFirstPage
   */
  public function testCreateVisibilityGroupWhenFirstSubPageSubPageCreated() {
    /** @var \Drupal\node\NodeInterface $book */
    $book = $this->createBookPage();

    /** @var \Drupal\node\NodeInterface $first_sub_page */
    $first_sub_page = $this->createBookPage([], $book->id());

    /** @var \Drupal\node\NodeInterface $first_sub_page_sub_page */
    $first_sub_page_sub_page = $this->createBookPage([], $book->id(), $first_sub_page->id());

    $this->assertNull(BlockVisibilityGroup::load("os_pages_{$book->id()}_{$first_sub_page_sub_page->id()}"));
  }

  /**
   * Tests if visibility group added when a second sub-page node is created.
   *
   * @covers ::shouldCreatePageVisibilityGroup
   * @covers ::isBookFirstPage
   */
  public function testCreateVisibilityGroupWhenSecondSubPageCreated() {
    /** @var \Drupal\node\NodeInterface $book */
    $book = $this->createBookPage();

    $this->createBookPage([], $book->id());

    /** @var \Drupal\node\NodeInterface $second_sub_page_sub_page */
    $second_sub_page_sub_page = $this->createBookPage([], $book->id());

    $this->assertNull(BlockVisibilityGroup::load("os_pages_{$book->id()}_{$second_sub_page_sub_page->id()}"));
  }

}
