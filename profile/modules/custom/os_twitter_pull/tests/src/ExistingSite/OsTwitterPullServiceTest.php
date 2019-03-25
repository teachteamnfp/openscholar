<?php

namespace Drupal\Tests\os_twitter_pull\ExistingSite;

use PHPUnit\Framework\MockObject\MockObject;
use Drupal\os_twitter_pull\TwitterPull;
use Drupal\os_twitter_pull\TwitterPullHandler;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Test base for os_twitter_pull.handler service tests.
 */
class OsTwitterPullServiceTest extends ExistingSiteBase {

  /**
   * Test os_twitter_pull.handler service.
   */
  public function testServiceDataParser() {
    $item_obj = new \stdClass();
    $item_obj->id_str = '1110075269652664321';
    $item_obj->from_user = 'user test 1';
    $item_obj->profile_image_url = 'http://example.com/image.png';
    $item_obj->profile_image_url_https = 'https://example.com/image.png';
    $item_obj->full_text = '#Harvard University #MBA #Scholarship

https://t.co/HHcBmhyrOE';
    $item_obj->created_at = 'Mon Mar 25 07:05:43 +0000 2019';
    $data_from_exchange = [
      $item_obj,
    ];
    $pullerMock = $this->getPullerMock($data_from_exchange);

    $service = new TwitterPullHandler(
      $this->container->get('os_twitter_pull.config'),
      $pullerMock,
      $this->container->get('cache.os_twitter_pull'),
      $this->container->get('logger.factory'),
      $this->container->get('datetime.time'),
      $this->container->get('request_stack'),
      $this->container->get('module_handler')
    );
    $test_items = $service->twitterPullRetrieve('Harvard', 3, 0);
    $this->assertSame('1110075269652664321', $test_items[0]->id);
    $this->assertSame('user test 1', $test_items[0]->username);
    $this->assertSame('http://example.com/image.png', $test_items[0]->userphoto);
    $this->assertSame('https://example.com/image.png', $test_items[0]->userphoto_https);
    $this->assertSame('#Harvard University #MBA #Scholarship

https://t.co/HHcBmhyrOE', $test_items[0]->text);
    $this->assertSame(1553497543, $test_items[0]->timestamp);
    $this->assertNull($test_items[0]->media_url);
  }

  /**
   * Get puller mock without real api call.
   *
   * @param array $data_from_exchange
   *   Mock data from exchange.
   *
   * @return \PHPUnit\Framework\MockObject\MockObject
   *   Mock object.
   */
  protected function getPullerMock(array $data_from_exchange): MockObject {
    $pullerMock = $this->getMockBuilder(TwitterPull::class)
      ->setMethods(['getItemsFromExchange'])
      ->getMock();
    $pullerMock->method('getItemsFromExchange')
      ->willReturn($data_from_exchange);
    return $pullerMock;
  }

}
