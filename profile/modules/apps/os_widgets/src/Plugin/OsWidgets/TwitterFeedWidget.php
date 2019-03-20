<?php

namespace Drupal\os_widgets\Plugin\OsWidgets;

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
    if (empty($block_content)) {
      return;
    }
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
    $field_twitter_followme_link_values = $block_content->get('field_twitter_followme_link')->getValue();
    $tweets = $this->twitterPullHandler->twitterPullRetrieve($twitkey, $field_twitter_num_items_values[0]['value'], !empty($field_twitter_exclude_retweets_values[0]['value']));

    $build['twitter']['#theme'] = 'os_widgets_twitter_pull';
    $build['twitter']['#tweets'] = $tweets;
    $build['twitter']['#is_follow_me'] = !empty($field_twitter_followme_link_values[0]['value']);
  }

}
