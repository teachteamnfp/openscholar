<?php

namespace Drupal\os_widgets\Plugin\OsWidgets;

use Drupal\Core\Url;
use Drupal\os_widgets\OsWidgetsBase;
use Drupal\os_widgets\OsWidgetsInterface;

/**
 * Class RssFeedWidget.
 *
 * @OsWidget(
 *   id = "rss_feed_widget",
 *   title = @Translation("RSS Feed")
 * )
 */
class RssFeedWidget extends OsWidgetsBase implements OsWidgetsInterface {

  /**
   * {@inheritdoc}
   */
  public function buildBlock(&$build, $block_content) {
    if (empty($block_content)) {
      return;
    }
    $field_is_show_all_content_values = $block_content->get('field_is_show_all_content')->getValue();

    $types = [];
    foreach ($block_content->field_content_types as $item) {
      if (!empty($item->value)) {
        $types[] = $item->value;
      }
    }
    $arg = '';
    if (empty($field_is_show_all_content_values[0]['value']) && count($types)) {
      $arg = '/' . implode("+", $types);
    }
    $build['rss_feed'] = [
      '#title' => t('RSS feed link!'),
      '#type' => 'link',
      '#url' => Url::fromUri('https://www.drupal.org/feed' . $arg),
    ];
  }

}
