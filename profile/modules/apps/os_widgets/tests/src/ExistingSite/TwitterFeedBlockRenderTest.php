<?php

namespace Drupal\Tests\os_widgets\ExistingSite;

use PHPUnit\Framework\MockObject\MockObject;
use Drupal\os_twitter_pull\TwitterPullHandler;
use Drupal\os_widgets\Plugin\OsWidgets\TwitterFeedWidget;

/**
 * Class TwitterFeedBlockRenderTest.
 *
 * @group kernel
 * @group widgets
 * @covers \Drupal\os_widgets\Plugin\OsWidgets\TwitterFeedWidget
 */
class TwitterFeedBlockRenderTest extends OsWidgetsExistingSiteTestBase {

  /**
   * Test build function display style buttons.
   */
  public function testBuildHashtag() {
    $item_obj = new \stdClass();
    $item_obj->id = '1110075269652664321';
    $item_obj->username = 'user test 1';
    $item_obj->userphoto = 'http://example.com/image.png';
    $item_obj->userphoto_https = 'https://example.com/image.png';
    $item_obj->text = '#Harvard University #MBA #Scholarship

https://t.co/HHcBmhyrOE';
    $item_obj->timestamp = 1553497543;
    $data_from_handler = [
      $item_obj,
    ];
    $twitter_feed_widget = $this->getTwitterWidgetMock($data_from_handler);

    /** @var \Drupal\block_content\Entity\BlockContent $block_content */
    $block_content = $this->createBlockContent([
      'type' => 'twitter_feed',
      'field_twitter_type' => [
        'hashtag',
      ],
      'field_twitter_username' => [
        'Harvard',
      ],
      'field_twitter_num_items' => [
        5,
      ],
    ]);
    $build = [];
    $twitter_feed_widget->buildBlock($build, $block_content);

    $this->assertEquals('os_widgets_twitter_pull', $build['twitter']['#theme']);
    $this->assertNotEmpty($build['twitter']['#tweets']);
    $this->assertEquals('<a href="http://twitter.com/#!/search?q=%23Harvard" title="#Harvard" rel="nofollow">#Harvard</a> University <a href="http://twitter.com/#!/search?q=%23MBA" title="#MBA" rel="nofollow">#MBA</a> <a href="http://twitter.com/#!/search?q=%23Scholarship" title="#Scholarship" rel="nofollow">#Scholarship</a>

<a href="https://t.co/HHcBmhyrOE" rel="nofollow" title="https://t.co/HHcBmhyrOE">t.co/HHcBmhyrOE</a>', $build['twitter']['#tweets'][0]->text);
    $this->assertFalse($build['twitter']['#is_follow_me']);
  }

  /**
   * Get TwitterWidget mock.
   *
   * With original methods and inject TwitterPullHandler Mock.
   *
   * @param array $data_from_exchange
   *   Mock data for twitterPullRetrieve().
   *
   * @return \PHPUnit\Framework\MockObject\MockObject
   *   TwitterFeedWidget mock object.
   */
  protected function getTwitterWidgetMock(array $data_from_exchange): MockObject {
    $pull_handler_mock = $this->getMockBuilder(TwitterPullHandler::class)
      ->disableOriginalConstructor()
      ->setMethods(['twitterPullRetrieve'])
      ->getMock();
    $pull_handler_mock->method('twitterPullRetrieve')
      ->willReturn($data_from_exchange);

    $twitter_feed_widget = $this->getMockBuilder(TwitterFeedWidget::class)
      ->setConstructorArgs([
        [],
        'test',
        [],
        $this->entityTypeManager,
        $this->container->get('database'),
        $pull_handler_mock,
      ])
      ->setMethods(NULL)
      ->getMock();

    return $twitter_feed_widget;
  }

}
