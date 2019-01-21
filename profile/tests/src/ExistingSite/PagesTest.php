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

}
