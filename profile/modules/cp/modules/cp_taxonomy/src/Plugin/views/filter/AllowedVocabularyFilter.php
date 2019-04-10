<?php

namespace Drupal\cp_taxonomy\Plugin\views\filter;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\views\Plugin\views\filter\FilterPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Filter a View for taxonomy term depends on vocabulary allowed values.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("allowed_vocabulary_filter")
 */
class AllowedVocabularyFilter extends FilterPluginBase {

  private $configFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->configFactory = $config_factory;
  }

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
   * Alter the query.
   */
  public function query() {

    // $this->query->addWhere('vsite', 'gid', $gids, 'IN');.
    $config_allowed_vocabulary_reference = $this->configFactory->get('cp_taxonomy.settings.allowed_vocabulary_reference_types')->get();
    $this->displayHandler->display['cache_metadata']['contexts'][] = 'vsite';
  }

}
