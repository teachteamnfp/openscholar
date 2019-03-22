<?php

namespace Drupal\Tests\os_twitter_pull\ExistingSite;

use Drupal\os_twitter_pull\TwitterPull;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Test base for os_twitter_pull.handler service tests.
 */
class OsTwitterPullServiceTest extends ExistingSiteBase {

  /**
   * Test os_twitter_pull.handler service.
   */
  public function testBasicService() {
    $container = new ContainerBuilder();
    $pullerMock = $this->createMock(TwitterPull::class);
    $pullerMock->method('getItemsFromExchange')
      ->willReturn([new \stdClass()]);

    $container->set('os_twitter_pull.puller', $pullerMock);
    $container->set('os_twitter_pull.handler', $this->container->get('os_twitter_pull.handler'));
    $container->set('module_handler', $this->container->get('module_handler'));
    \Drupal::setContainer($container);

    $service = \Drupal::service('os_twitter_pull.handler');
    $test_items = $service->twitterPullRetrieve('Harvard', 3, 0);
    $this->assertSame([new \stdClass()], $test_items);
  }

}
