<?php

namespace Drupal\vsite_infinite_scroll\Plugin\views\pager;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\views_infinite_scroll\Plugin\views\pager\InfiniteScroll;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
   * Contains the configuration object factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory')
    );
  }

  /**
   * Constructs a TestProcessor object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   The configuration factory object.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config) {
    $this->configFactory = $config;
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function render($input) {
    $config = $this->configFactory->get('vsite_infinite_scroll.settings');
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
