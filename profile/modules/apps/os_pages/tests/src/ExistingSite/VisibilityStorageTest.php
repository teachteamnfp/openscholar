<?php

namespace Drupal\Tests\os_pages\ExistingSite;

use Drupal\block_visibility_groups\Entity\BlockVisibilityGroup;

/**
 * Tests VisibilityStorage.
 *
 * @group openscholar
 * @group kernel
 * @group other
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

    $visibility_group = BlockVisibilityGroup::load("os_pages_page_{$book->id()}");

    $this->assertNotNull($visibility_group);

    /** @var array $conditions */
    $conditions = array_values($visibility_group->getConditions()->getConfiguration());
    array_walk($conditions, function (&$condition) {
      unset($condition['uuid']);
    });

    $this->assertTrue(in_array([
      'id' => 'node_type',
      'bundles' => [
        $book->bundle() => $book->bundle(),
      ],
      'negate' => FALSE,
      'context_mapping' => [
        'node' => '@node.node_route_context:node',
      ],
    ], $conditions));
  }

}
