<?php


namespace Drupal\vsite_infinite_scroll\Plugin\views\pager;

use Drupal\views_infinite_scroll\Plugin\views\pager\InfiniteScroll;

/**
 * Views pager plugin to handle infinite scrolling.
 *
 * @ViewsPager(
 *  id = "vsite_infinite_scroll",
 *  title = @Translation("Vsite Infinite Scroll"),
 *  short_title = @Translation("Vsite Infinite Scroll"),
 *  help = @Translation("Override views infinite scroll."),
 *  theme = "views_infinite_scroll_pager"
 * )
 */
class VsiteInfiniteScroll extends InfiniteScroll {

  /**
   * {@inheritdoc}
   */
  public function render($input) {

    $config = \Drupal::config('vsite_infinite_scroll.setting');
    $long_list_content_pagination = $config->get('long_list_content_pagination');
    if ($long_list_content_pagination == 'pager') {
      // The 1, 3 indexes are correct, see template_preprocess_pager().
      $tags = [
        1 => $this->options['tags']['previous'],
        3 => $this->options['tags']['next'],
      ];
      return [
        '#theme' => $this->view->buildThemeFunctions('views_mini_pager'),
        '#tags' => $tags,
        '#element' => $this->options['id'],
        '#parameters' => $input,
        '#route_name' => '<none>',
      ];
    }

    return [
      '#theme' => $this->themeFunctions(),
      '#options' => $this->options['views_infinite_scroll'],
      '#attached' => [
        'library' => ['views_infinite_scroll/views-infinite-scroll'],
      ],
      '#element' => $this->options['id'],
      '#parameters' => $input,
    ];
  }

}
