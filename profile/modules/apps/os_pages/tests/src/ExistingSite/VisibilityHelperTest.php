<?php

namespace Drupal\Tests\os_pages;

use Drupal\block_visibility_groups\Entity\BlockVisibilityGroup;
use Drupal\Tests\openscholar\ExistingSite\TestBase;

/**
 * Tests for VisibilityHelper.
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

  /**
   * Test addition of new condition in section group.
   */
  public function testNewSectionVisibilityGroupCondition() {
    /** @var \Drupal\node\NodeInterface $book */
    $book = $this->createBookPage();

    /** @var \Drupal\node\NodeInterface $first_sub_page */
    $first_sub_page = $this->createBookPage([], $book->id());

    /** @var \Drupal\node\NodeInterface $first_sub_sub_page */
    $first_sub_sub_page = $this->createBookPage([], $book->id(), $first_sub_page->id());

    /** @var \Drupal\node\NodeInterface $second_sub_page */
    $second_sub_page = $this->createBookPage([], $book->id());

    $section_visibility_group = BlockVisibilityGroup::load("os_pages_section_{$book->id()}");

    /** @var array $conditions */
    $conditions = array_values($section_visibility_group->getConditions()->getConfiguration());
    array_walk($conditions, function (&$condition) {
      unset($condition['uuid']);
    });

    $this->assertTrue(in_array([
      'id' => 'request_path',
      'pages' => "/node/{$first_sub_sub_page->id()}",
      'negate' => FALSE,
    ], $conditions));

    $this->assertTrue([
      'id' => 'request_path',
      'pages' => "/node/{$second_sub_page->id()}",
      'negate' => FALSE,
    ], $conditions);
  }

}
