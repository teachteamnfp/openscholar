<?php

namespace Drupal\Tests\os_widgets\ExistingSite;

use Drupal\os_widgets\Plugin\OsWidgets\FeaturedPostsWidget;

/**
 * Class FeaturedPosts.
 *
 * @group kernel
 * @group widgets
 * @covers \Drupal\os_widgets\Plugin\OsWidgets\FeaturedPostsWidget
 */
class FeaturedPostsBlockRenderTest extends OsWidgetsExistingSiteTestBase {

  /**
   * The object we're testing.
   *
   * @var \Drupal\os_widgets\Plugin\OsWidgets\FeaturedPostsWidget
   */
  protected $featuredPostsWidget;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->featuredPostsWidget = $this->osWidgets->createInstance('featured_posts_widget');
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
    $build = [];
    $this->featuredPostsWidget->buildBlock($build, $block_content);
    $this->assertSame([], $build, 'Build block should not modify the build.');
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
    $build = [];
    $this->featuredPostsWidget->buildBlock($build, $block_content);
    $this->assertSame('teaser', $build['field_featured_posts'][0]['#view_mode']);
    $this->assertSame('styled', $build['#extra_classes'][0]);
    $this->assertSame(FALSE, $build['field_featured_posts'][0]['os_widgets_hide_node_title']);
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
    $build = [];
    $this->featuredPostsWidget->buildBlock($build, $block_content);
    $this->assertSame('teaser', $build['field_featured_posts'][0]['#view_mode']);
    $this->assertSame(TRUE, $build['field_featured_posts'][0]['os_widgets_hide_node_title']);
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
    $randomDeltaId = 1;
    $featured_posts_widget = $this->getMockBuilder(FeaturedPostsWidget::class)
      ->setConstructorArgs([[], 'test', [], $this->entityTypeManager])
      ->setMethods(['shortRandom'])
      ->getMock();
    $featured_posts_widget->method('shortRandom')
      ->willReturn($randomDeltaId);
    $build = [];
    $featured_posts_widget->buildBlock($build, $block_content);
    $this->assertSame(FALSE, $build['field_featured_posts'][0]['#access']);
    $this->assertFalse(array_key_exists('#access', $build['field_featured_posts'][$randomDeltaId]));
  }

}
