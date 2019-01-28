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
   * Tests if section visibility group added when a new book is created.
   *
   * @covers ::isBookFirstPage
   */
  public function testCreateSectionVisibilityGroupWhenBookCreated() {
    /** @var \Drupal\node\NodeInterface $book */
    $book = $this->createBookPage();

    $this->assertNull(BlockVisibilityGroup::load("os_pages_section_{$book->id()}"));
  }

  /**
   * Tests if section visibility group added when a non-page node created.
   *
   * @covers ::isBookFirstPage
   */
  public function testCreateSectionVisibilityGroupWhenNonPageNodeCreated() {
    /** @var \Drupal\node\NodeInterface $book */
    $book = $this->createBookPage();

    $this->createNode([
      'type' => 'event',
    ]);

    $this->assertNull(BlockVisibilityGroup::load("os_pages_section_{$book->id()}"));
  }

  /**
   * Tests if section visibility group added when first sub-page is created.
   *
   * @covers ::isBookFirstPage
   */
  public function testCreateSectionVisibilityGroupWhenFirstSubPageNodeCreated() {
    /** @var \Drupal\node\NodeInterface $book */
    $book = $this->createBookPage();

    $this->createBookPage([], $book->id());

    $this->assertNotNull(BlockVisibilityGroup::load("os_pages_section_{$book->id()}"));
  }

  /**
   * Tests page visibility group create.
   */
  public function testCreatePageVisibilityGroup() {
    /** @var \Drupal\node\NodeInterface $book */
    $book = $this->createBookPage();

    $this->assertNotNull(BlockVisibilityGroup::load("os_pages_page_{$book->id()}"));

    /** @var \Drupal\node\NodeInterface $event */
    $event = $this->createNode([
      'type' => 'event',
    ]);

    $this->assertNull(BlockVisibilityGroup::load("os_pages_page_{$event->id()}"));
  }

}
