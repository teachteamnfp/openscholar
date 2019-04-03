<?php

namespace Drupal\os_widgets\Plugin\OsWidgets;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\os_twitter_pull\TwitterPullHandler;
use Drupal\os_widgets\OsWidgetsBase;
use Drupal\os_widgets\OsWidgetsInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class TwitterFeedWidget.
 *
 * @OsWidget(
 *   id = "twitter_feed_widget",
 *   title = @Translation("Twitter Feed")
 * )
 */
class TwitterFeedWidget extends OsWidgetsBase implements OsWidgetsInterface {

  protected $twitterPullHandler;

  /**
   * {@inheritdoc}
   */
  public function __construct($configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, TwitterPullHandler $twitter_pull_handler) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager);
    $this->twitterPullHandler = $twitter_pull_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('os_twitter_pull.handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildBlock(&$build, $block_content) {
    $field_twitter_username_values = $block_content->get('field_twitter_username')->getValue();
    $twitkey = $field_twitter_username_values[0]['value'];

    if ($twitkey[0] != '@' || $twitkey[0] != '#') {
      $field_twitter_type_values = $block_content->get('field_twitter_type')->getValue();
      switch ($field_twitter_type_values[0]['value']) {
        case 'user':
          $twitkey = '@' . $twitkey;
          break;

        case 'hashtag':
          $twitkey = '#' . $twitkey;
          break;
      }
    }

    $field_twitter_num_items_values = $block_content->get('field_twitter_num_items')->getValue();
    $field_twitter_exclude_retweets_values = $block_content->get('field_twitter_exclude_retweets')->getValue();
    $field_twitter_is_followme_link_values = $block_content->get('field_twitter_is_followme_link')->getValue();
    $tweets = $this->twitterPullHandler->twitterPullRetrieve($twitkey, $field_twitter_num_items_values[0]['value'], !empty($field_twitter_exclude_retweets_values[0]['value']));

    foreach ($tweets as &$tweet) {
      $tweet->text = $this->addLinks($tweet->text);
    }

    $build['twitter']['#theme'] = 'os_widgets_twitter_pull';
    $build['twitter']['#tweets'] = $tweets;
    $build['twitter']['#is_follow_me'] = !empty($field_twitter_is_followme_link_values[0]['value']);
    $build['twitter']['#follow_me_link'] = $block_content->get('field_twitter_followme_link')->view([
      'label' => 'hidden',
    ]);
  }

  /**
   * Automatically add links to URLs and Twitter usernames in a tweet.
   */
  private function addLinks($text) {
    $pattern = '#(https?)://([^\s\(\)\,]+)#ims';
    $repl = '<a href="$1://$2" rel="nofollow" title="$1://$2">$2</a>';
    $text = preg_replace($pattern, $repl, $text);

    $pattern = '#@(\w+)#ims';
    $repl = '<a href="http://twitter.com/$1" rel="nofollow" title="@$1">@$1</a>';
    $text = preg_replace($pattern, $repl, $text);

    $pattern = '/[#]+([A-Za-z0-9-_]+)/';
    $repl = '<a href="http://twitter.com/#!/search?q=%23$1" title="#$1" rel="nofollow">#$1</a>';
    $text = preg_replace($pattern, $repl, $text);

    return Xss::filter($text);
  }

}
