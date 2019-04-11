<?php

namespace Drupal\cp_taxonomy\Plugin\views\filter;

use Drupal\cp_taxonomy\CpTaxonomyHelperInterface;
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

  private $cpTaxonomyHelper;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, CpTaxonomyHelperInterface $cp_taxonomy_helper) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->cpTaxonomyHelper = $cp_taxonomy_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('cp_taxonomy.helper')
    );
  }

  /**
   * Alter the query.
   */
  public function query() {
    $settings = $this->cpTaxonomyHelper->getTaxonomyTermSettingsFromRequest();
    if (!empty($settings['bundle_key'])) {
      $vocabularies = $this->cpTaxonomyHelper->searchAllowedVocabulariesByType($settings['bundle_key']);
      if (!empty($vocabularies)) {
        $this->query->addWhere('cp_taxonomy', 'vid', array_values($vocabularies), 'IN');
      }
    }
  }

}
