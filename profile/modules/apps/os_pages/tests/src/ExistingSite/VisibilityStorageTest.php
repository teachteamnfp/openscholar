<?php

namespace Drupal\Tests\os_pages\ExistingSite;

use Drupal\block_visibility_groups\Entity\BlockVisibilityGroup;

/**
 * Tests VisibilityStorage.
 *
 * @group openscholar
 * @group kernel
 * @coversDefaultClass \Drupal\os_pages\VisibilityStorage
 */
class VisibilityStorageTest extends TestBase {

  /**
   * Tests creation of block visibility group.
   *
   * @covers ::create
   */
  public function testCreate() {
    /** @var \Drupal\node\NodeInterface $book */
    $book = $this->createBookPage();

    $this->assertNotNull(BlockVisibilityGroup::load("os_pages_page_{$book->id()}"));
  }

}
