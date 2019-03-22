<?php

namespace Drupal\os_twitter_pull;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Xss;

/**
 * @file
 * Twitter pull class implementation.
 */

/**
 * Class TwitterPull.
 *
 * @package Drupal\os_twitter_pull
 */
class TwitterPull {

  private $twitkey;
  private $numItems;
  private $excludeRetweets;
  private $tweets;
  private $settings;

  /**
   * Construct.
   *
   * @param TwitterPullConfig $config
   *   Twitter pull config.
   */
  public function __construct(TwitterPullConfig $config) {
    $this->settings = $config->getSettings();
  }

  /**
   * Set twitkey value.
   */
  public function setTwitkey($twitkey): void {
    $this->twitkey = $twitkey;
  }

  /**
   * Set numItems value.
   */
  public function setNumItems($numItems): void {
    $this->numItems = $numItems;
  }

  /**
   * Set excludeRetweets value.
   */
  public function setExcludeRetweets(bool $excludeRetweets): void {
    $this->excludeRetweets = $excludeRetweets;
  }

  /**
   * Get tweet items.
   *
   * @throws \Exception
   */
  public function getItems() {

    $prefix = mb_substr($this->twitkey, 0, 1);
    $slash = strpos($this->twitkey, '/', 1);
    $num = intval($this->numItems);

    // Lists have the format @username/listname.
    if ($prefix == '@' && $slash !== FALSE) {
      $listname = mb_substr($this->twitkey, $slash + 1);
      $url = 'https://api.twitter.com/1.1/lists/show.json';
      $query = '?slug=' . urlencode($listname);
    }
    // If the first character is @, then consider the key a username.
    elseif ($prefix == "@") {
      $key = mb_substr($this->twitkey, 1);
      $url = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
      if ($this->excludeRetweets) {
        $query = "?screen_name=${key}&count=${num}&include_rts=false";
      }
      else {
        $query = "?screen_name=${key}&count=${num}&include_rts=true";
      }
    }
    // If the first character is ~, then consider the key a favorites feed.
    elseif ($prefix == "~") {
      $key = mb_substr($this->twitkey, 1);
      $url = 'https://api.twitter.com/1.1/favorites/list.json';
      $query = "?screen_name=${key}&count=${num}";
    }
    // Otherwise, use the key as a search term.
    else {
      if ($num > 200) {
        $num = 200;
      }
      $url = 'https://api.twitter.com/1.1/search/tweets.json';
      if ($this->excludeRetweets) {
        $query = '?q=-filter:retweets+' . urlencode($this->twitkey) . "&include_entities=true&count=${num}";
      }
      else {
        $query = '?q=' . urlencode($this->twitkey) . "&include_entities=true&count=${num}";
      }
    }

    $query .= '&tweet_mode=extended';

    $items = $this->getItemsFromExchange($query, $url);

    $this->parseItems($items);

    return $this->tweets;
  }

  /**
   * Parse tweet items.
   */
  private function parseItems($items) {
    $tweets = [];

    // If search response then items are one level lower.
    if (isset($items->statuses) && is_array($items->statuses)) {
      $items = $items->statuses;
    }

    if (is_array($items)) {
      $items = array_slice($items, 0, $this->numItems);
      foreach ($items as $item) {
        $obj = new \stdClass();

        if (isset($item->retweeted_status)) {
          $obj->id = Html::escape($item->retweeted_status->id_str);
          $obj->username = (isset($item->retweeted_status->user) && !empty($item->retweeted_status->user->screen_name)) ? $item->retweeted_status->user->screen_name : $item->retweeted_status->from_user;
          $obj->username = Html::escape($obj->username);
          // Get the user photo for the retweet.
          $obj->userphoto = (isset($item->retweeted_status->user) && !empty($item->retweeted_status->user->profile_image_url)) ? $item->retweeted_status->user->profile_image_url : $item->retweeted_status->profile_image_url;
          $obj->userphoto = Html::escape($obj->userphoto);
          $obj->userphoto_https = (isset($item->retweeted_status->user) && !empty($item->retweeted_status->user->profile_image_url_https)) ? $item->retweeted_status->user->profile_image_url_https : $item->retweeted_status->profile_image_url_https;
          $obj->userphoto_https = Html::escape($obj->userphoto_https);

          $obj->text = Xss::filter($item->retweeted_status->full_text);
          // Convert date to unix timestamp so themer can easily work with it.
          $obj->timestamp = strtotime($item->retweeted_status->created_at);

          $obj->media_url = (isset($item->entities->media[0]->media_url) ? $item->entities->media[0]->media_url : NULL);
        }
        else {
          $obj->id = Html::escape($item->id_str);
          $obj->username = (isset($item->user) && !empty($item->user->screen_name)) ? $item->user->screen_name : $item->from_user;
          $obj->username = Html::escape($obj->username);
          // Retrieve the user photo.
          $obj->userphoto = (isset($item->user) && !empty($item->user->profile_image_url)) ? $item->user->profile_image_url : $item->profile_image_url;
          $obj->userphoto = Html::escape($obj->userphoto);
          $obj->userphoto_https = (isset($item->user) && !empty($item->user->profile_image_url_https)) ? $item->user->profile_image_url_https : $item->profile_image_url_https;
          $obj->userphoto_https = Html::escape($obj->userphoto_https);

          $obj->text = Xss::filter($item->full_text);
          // Convert date to unix timestamp so themer can easily work with it.
          $obj->timestamp = strtotime($item->created_at);

          $obj->media_url = (isset($item->entities->media[0]->media_url) ? $item->entities->media[0]->media_url : NULL);
        }
        $tweets[] = $obj;
      }
    }

    $this->tweets = $tweets;
  }

  /**
   * Get items from exchange.
   *
   * @param string $query
   *   Query with filters and parameters.
   * @param string $url
   *   Twitter API url.
   *
   * @return mixed
   *   List of returned object or null on decode error.
   *
   * @throws \Exception
   */
  public function getItemsFromExchange(string $query, string $url) {
    $twitter = new \TwitterAPIExchange($this->settings);
    $req = $twitter->setGetfield($query)
      ->buildOauth($url, 'GET')
      ->performRequest();
    $items = json_decode($req);
    return $items;
  }

}
