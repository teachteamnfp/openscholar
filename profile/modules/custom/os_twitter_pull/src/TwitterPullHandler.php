<?php

namespace Drupal\os_twitter_pull;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Extension\ModuleHandler;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class TwitterPullHandler.
 *
 * @package Drupal\os_twitter_pull
 */
class TwitterPullHandler implements ContainerInjectionInterface {

  use StringTranslationTrait;

  private $puller;
  private $config;
  private $cache;
  private $logger;
  private $time;
  private $currentRequest;
  private $moduleHandler;
  private $messenger;

  /**
   * TwitterPullHandler constructor.
   *
   * @param TwitterPullConfig $twitter_pull_config
   *   Twitter pull config.
   * @param \Drupal\os_twitter_pull\TwitterPull $twitter_pull
   *   Twitter pull.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache backend for os_twitter_pull.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Logger channel factory.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   Helper for get current time.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Request stack.
   * @param \Drupal\Core\Extension\ModuleHandler $module_handler
   *   Module handler.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   Core Messenger.
   */
  public function __construct(TwitterPullConfig $twitter_pull_config, TwitterPull $twitter_pull, CacheBackendInterface $cache, LoggerChannelFactoryInterface $logger_factory, TimeInterface $time, RequestStack $request_stack, ModuleHandler $module_handler, MessengerInterface $messenger) {
    $this->puller = $twitter_pull;
    $this->puller->setSettings($twitter_pull_config);
    $this->config = $twitter_pull_config;
    $this->cache = $cache;
    $this->time = $time;
    $this->logger = $logger_factory->get('os_twitter_pull');
    $this->currentRequest = $request_stack->getCurrentRequest();
    $this->moduleHandler = $module_handler;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('os_twitter_pull.config'),
      $container->get('os_twitter_pull.puller'),
      $container->get('cache.os_twitter_pull'),
      $container->get('logger.factory'),
      $container->get('datetime.time'),
      $container->get('request_stack'),
      $container->get('module_handler'),
      $container->get('messenger')
    );
  }

  /**
   * Retrieves tweets by username, hashkey or search term.
   *
   * @param string $twitkey
   *   Twitter key, which can be a username (prepended with @), hashtag
   *   (prepended with #), or a search term.
   * @param int $num_items
   *   Number of tweets to retrieve from Twitter. Can't be more than 200.
   * @param bool $exclude_retweets
   *   Exclude retweets.
   *
   * @return array
   *   Return an array of objects what are store filtered tweet properties.
   */
  public function twitterPullRetrieve($twitkey, $num_items, $exclude_retweets) {

    $this->puller->setTwitkey($twitkey);
    $this->puller->setNumItems($num_items);
    $this->puller->setExcludeRetweets($exclude_retweets);

    // Cached value is specific to the Twitter
    // key and number of tweets retrieved.
    $cache_key = $twitkey . '::' . $num_items . '::' . (int) $exclude_retweets;
    $cache = $this->cache->get($cache_key);

    $tweets = [];
    if (!empty($cache) && !empty($cache->data)) {
      $tweets = $cache->data;
    }
    else {
      try {
        $tweets = $this->puller->getItems();
      }
      catch (\Exception $e) {
        $this->logger->warning($e->getMessage());
        $this->messenger->addError($this->t('Unable to retrieve the tweets.'));
        if (!empty($cache) && !empty($cache->data)) {
          return $cache->data;
        }
      }

      if (!empty($tweets) && is_array($tweets)) {
        $cache_length = $this->config->getCacheLength();
        $this->cache->set($cache_key, $tweets, $this->time->getCurrentTime() + $cache_length);
      }
    }

    preg_match("/@(\w+)/", $twitkey, $matches);
    if (isset($matches[1])) {
      $twitkey_username = $matches[1];
    }

    // If the tweet is not ours, flag it as a retweet.
    if (isset($twitkey_username)) {
      foreach ($tweets as $i => $tweet) {
        $tweets[$i]->is_retweet = FALSE;
        if ($tweets[$i]->username != $twitkey_username) {
          $tweets[$i]->is_retweet = TRUE;
        }
      }
    }

    // If we have tweets and are viewing a secure site, we want to set the url
    // to the userphoto to use the secure image to avoid insecure errors.
    if (!empty($tweets) && is_array($tweets) && $this->currentRequest->isSecure()) {
      foreach ($tweets as $i => $tweet) {
        $tweets[$i]->userphoto = $tweet->userphoto_https;
      }
    }

    $this->moduleHandler->alter('os_twitter_pull_retrieve', $tweets);

    return $tweets;
  }

}
