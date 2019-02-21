<?php

namespace Drupal\Tests\os_widgets\ExistingSite;

use Drupal\os_widgets\BlockContentType\FeaturedPostsWidget;

/**
 * Class FeaturedPosts.
 *
 * @group kernel
 * @covers \Drupal\os_widgets\BlockContentType\FeaturedPostsWidget
 */
class FeaturedPostsBlockRenderTest extends OsWidgetsExistingSiteTestBase {

  /**
   * The object we're testing.
   *
   * @var \Drupal\os_widgets\BlockContentType\FeaturedPostsWidget
   */
  protected $featuredPostsWidget;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->featuredPostsWidget = new FeaturedPostsWidget();
  }

  /**
   * Test title view mode without styled.
   */
  public function testBuildSimpleTitle() {
    $node = $this->createNode();

    $block_content = $this->createBlockContent([
      'type' => 'featured_posts',
      'field_featured_posts' => [
        $node,
      ],
      'field_display_style' => [
        'title',
      ],
    ]);
    $variables = $this->featuredPostsWidget->buildBlock([], $block_content);
    $this->assertSame([], $variables, 'Build block should not modify the variables.');
  }

  /**
   * Test empty input parameters.
   */
  public function testBuildEmptyBlockOrEmptyPosts() {

    $variables = $this->featuredPostsWidget->buildBlock([], NULL);
    $this->assertSame([], $variables, 'Build empty block should not modify the variables.');

    $block_content = $this->createBlockContent([
      'type' => 'featured_posts',
      'field_display_style' => [
        'title',
      ],
    ]);
    $variables = $this->featuredPostsWidget->buildBlock([], $block_content);
    $this->assertSame([], $variables, 'Build block with empty posts should not modify the variables.');
  }

  /**
   * Test teaser and styled output.
   */
  public function testBuildTeaserStyledPosts() {
    $node = $this->createNode();

    $block_content = $this->createBlockContent([
      'type' => 'featured_posts',
      'field_featured_posts' => [
        $node,
      ],
      'field_display_style' => [
        'teaser',
      ],
      'field_is_random' => [
        0,
      ],
      'field_is_styled' => [
        1,
      ],
    ]);
    $variables = $this->featuredPostsWidget->buildBlock([], $block_content);
    $this->assertSame('teaser', $variables['content']['field_featured_posts'][0]['#view_mode']);
    $this->assertSame('styled', $variables['attributes']['class'][0]);
    $this->assertSame(FALSE, $variables['content']['field_featured_posts'][0]['os_widgets_hide_node_title']);
  }

  /**
   * Test teaser and hide title output.
   */
  public function testBuildTeaserHideTitlePosts() {
    $node = $this->createNode();

    $block_content = $this->createBlockContent([
      'type' => 'featured_posts',
      'field_featured_posts' => [
        $node,
      ],
      'field_display_style' => [
        'teaser',
      ],
      'field_hide_title' => [
        1,
      ],
    ]);
    $variables = $this->featuredPostsWidget->buildBlock([], $block_content);
    $this->assertSame('teaser', $variables['content']['field_featured_posts'][0]['#view_mode']);
    $this->assertSame(TRUE, $variables['content']['field_featured_posts'][0]['os_widgets_hide_node_title']);
  }

  /**
   * Test random output with mock.
   */
  public function testBuildRandomPosts() {
    $node1 = $this->createNode();
    $node2 = $this->createNode();

    $block_content = $this->createBlockContent([
      'type' => 'featured_posts',
      'field_featured_posts' => [
        $node1,
        $node2,
      ],
      'field_display_style' => [
        'teaser',
      ],
      'field_is_random' => [
        1,
      ],
    ]);
    $featured_posts_widget = $this->getMockBuilder(FeaturedPostsWidget::class)
      ->setMethods(['shortRandom'])
      ->getMock();
    $featured_posts_widget->method('shortRandom')
      ->willReturn(1);
    $variables = $featured_posts_widget->buildBlock([], $block_content);
    $this->assertSame(FALSE, $variables['content']['field_featured_posts'][0]['#access']);
    $this->assertFalse(array_key_exists('#access', $variables['content']['field_featured_posts'][1]));
  }

}
