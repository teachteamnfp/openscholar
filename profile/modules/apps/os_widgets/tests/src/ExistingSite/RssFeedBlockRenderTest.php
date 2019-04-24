<?php

namespace Drupal\Tests\os_widgets\ExistingSite;

use Drupal\views\Views;

/**
 * Class RssFeedBlockRenderTest.
 *
 * @group kernel
 * @group widgets
 * @covers \Drupal\os_widgets\Plugin\OsWidgets\RssFeedWidget
 */
class RssFeedBlockRenderTest extends OsWidgetsExistingSiteTestBase {

  /**
   * Test render feed link.
   */
  public function testRenderFeedLinkAllContentTypes() {
    /** @var \Drupal\block_content\Entity\BlockContent $block_content */
    $block_content = $this->createBlockContent([
      'type' => 'rss_feed',
      'field_content_to_display' => [],
      'field_is_show_all_content' => [
        TRUE,
      ],
    ]);
    $view_builder = $this->entityTypeManager
      ->getViewBuilder('block_content');
    $render = $view_builder->view($block_content);

    $this->assertSame('os_widgets/rss_feed_copy', $render['rss_feed']['#attached']['library'][0]);
    $this->assertSame('link', $render['rss_feed']['#type']);
    $this->assertSame('rss-feed-link', $render['rss_feed']['#attributes']['class'][0]);
    $this->assertSame('route:view.os_feeds.feed_1;arg_0=', $render['rss_feed']['#url']->toUriString());
  }

  /**
   * Test render feed link with contenty types.
   */
  public function testRenderFeedLinkFilteredContentTypes() {
    /** @var \Drupal\block_content\Entity\BlockContent $block_content */
    $block_content = $this->createBlockContent([
      'type' => 'rss_feed',
      'field_content_to_display' => [
        'class',
        'link',
        'news',
      ],
      'field_is_show_all_content' => [
        FALSE,
      ],
    ]);
    $view_builder = $this->entityTypeManager
      ->getViewBuilder('block_content');
    $render = $view_builder->view($block_content);

    $this->assertSame('route:view.os_feeds.feed_1;arg_0=class%2Blink%2Bnews', $render['rss_feed']['#url']->toUriString());
  }

  /**
   * Test feed URL serving view access.
   */
  public function testFeedUrlViewAccess() {
    $this->visit('/feed');
    $this->assertSession()->statusCodeEquals(200);
    $this->visit('/feed/events%2Bfaq');
    $this->assertSession()->statusCodeEquals(200);
  }

  /**
   * Test field allowed values is valid array.
   */
  public function testFieldAllowedTypesFunction() {
    $field_allowed_values = os_widgets_field_content_to_display_allowed_values();

    $this->assertNotEmpty($field_allowed_values);
    $this->assertArrayHasKey('link', $field_allowed_values);
    $this->assertSame('Link', $field_allowed_values['link']);
    $this->assertArrayHasKey('all_publications', $field_allowed_values);
    $this->assertSame('Publications', $field_allowed_values['all_publications']->__tostring());
  }

  /**
   * Testing os_feeds view to merge publications on proper argument.
   */
  public function testMergingViewsWithPublications() {
    $news1 = $this->createNode([
      'type' => 'news',
      'created' => 1555593820,
    ]);
    $this->createReference([
      'title' => 'Reference1',
      'created' => 1555593821,
    ]);
    $this->createReference([
      'title' => 'Reference2',
      'created' => 1555593822,
    ]);
    $view = Views::getView('os_feeds');
    $view->setArguments(['news+all_publications']);
    $view->setDisplay('feed_1');
    $view->preExecute();
    $view->execute();
    $output = $view->preview();
    $this->assertCount(3, $output['#rows']);
    $this->assertEquals('Reference2', $output['#rows'][0]['#row']->title->__toString());
    $this->assertEquals('Reference1', $output['#rows'][1]['#row']->title->__toString());
    $this->assertEquals($news1->getTitle(), $output['#rows'][2]['#row']->title);
  }

}
