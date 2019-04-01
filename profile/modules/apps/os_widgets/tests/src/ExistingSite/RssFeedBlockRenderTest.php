<?php

namespace Drupal\Tests\os_widgets\ExistingSite;

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
      'field_content_types' => [],
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
    $this->assertSame('https://www.drupal.org/feed', $render['rss_feed']['#url']->getUri());
  }

  /**
   * Test render feed link with contenty types.
   */
  public function testRenderFeedLinkFilteredContentTypes() {
    /** @var \Drupal\block_content\Entity\BlockContent $block_content */
    $block_content = $this->createBlockContent([
      'type' => 'rss_feed',
      'field_content_types' => [
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

    $this->assertSame('https://www.drupal.org/feed/class+link+news', $render['rss_feed']['#url']->getUri());
  }

  /**
   * Test field allowed values is valid array.
   */
  public function testFieldAllowedTypesFunction() {
    $field_allowed_values = os_widgets_field_content_types_allowed_values();

    $this->assertNotEmpty($field_allowed_values);
    $this->assertArrayHasKey('link', $field_allowed_values);
    $this->assertSame('Link', $field_allowed_values['link']);
  }

}
