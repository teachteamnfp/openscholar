<?php

namespace Drupal\Tests\os_pages\ExistingSiteJavascript;

use Behat\Mink\Exception\ExpectationException;
use Drupal\block\Entity\Block;

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
    /** @var \Drupal\Core\Config\ImmutableConfig $theme_config */
    $theme_config = \Drupal::config('system.theme');

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

    $section_block = Block::create([
      'id' => "booknavigation_{$book1->id()}",
      'theme' => $theme_config->get('default'),
      'region' => 'sidebar_second',
      'plugin' => 'book_navigation',
      'settings' => [
        'id' => 'book_navigation',
        'label' => 'Books',
        'provider' => 'book',
        'label_display' => 'visible',
        'block_mode' => 'all pages',
      ],
      'visibility' => [
        'condition_group' => [
          'id' => 'condition_group',
          'negate' => FALSE,
          'block_visibility_group' => "os_pages_section_{$book1->id()}",
          'context_mapping' => [],
        ],
      ],
    ]);
    $section_block->save();

    $page_block = Block::create([
      'id' => "entityviewcontent_{$page1->id()}",
      'theme' => $theme_config->get('default'),
      'region' => 'sidebar_second',
      'plugin' => 'entity_view:node',
      'settings' => [
        'id' => 'entity_view:node',
        'label' => $page1->label(),
        'provider' => 'ctools',
        'label_display' => '0',
        'view_mode' => 'block',
        'context_mapping' => [
          'entity' => '@node.node_route_context:node',
        ],
      ],
      'visibility' => [
        'condition_group' => [
          'id' => 'condition_group',
          'negate' => FALSE,
          'block_visibility_group' => "os_pages_page_{$page1->id()}",
          'context_mapping' => [],
        ],
      ],
    ]);
    $page_block->save();

    $web_assert = $this->assertSession();

    try {
      $this->visit($path_alias_manager->getAliasByPath("/node/{$book1->id()}"));

      $this->assertNotNull($web_assert->elementExists('css', '.block-book-navigation'));
      $web_assert->pageTextContains($book1->label());
      $web_assert->pageTextContains($page1->label());
      $web_assert->pageTextContains($book2->label());

      $this->visit($path_alias_manager->getAliasByPath("/node/{$page1->id()}"));

      $this->assertNotNull($web_assert->elementExists('css', '.block-book-navigation'));
      $web_assert->pageTextContains($book1->label());
      $web_assert->pageTextContains($page1->label());
      $web_assert->pageTextContains($book2->label());

      $this->assertNotNull($web_assert->elementExists('css', '.block-entity-viewnode'));
      $web_assert->pageTextContains($page1->get('body')->first()->getValue()['value']);

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
