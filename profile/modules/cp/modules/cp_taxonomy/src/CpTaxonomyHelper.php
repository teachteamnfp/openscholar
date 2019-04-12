<?php

namespace Drupal\cp_taxonomy;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\taxonomy\Entity\Vocabulary;

/**
 * Helper functions to handle vocabularies and related entities.
 */
class CpTaxonomyHelper implements CpTaxonomyHelperInterface {

  private $configFactory;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public function searchAllowedVocabulariesByType(string $bundle_key): array {
    $vsite_vocabularies = Vocabulary::loadMultiple();
    $filter_vocabularies = [];
    foreach ($vsite_vocabularies as $vid => $vocabulary) {
      $config_vocab = $this->configFactory->getEditable('taxonomy.vocabulary.' . $vid);
      $bundle_keys = $config_vocab->get('allowed_vocabulary_reference_types');
      if (empty($bundle_keys)) {
        continue;
      }
      if (in_array($bundle_key, $bundle_keys)) {
        $filter_vocabularies[$vid] = $vid;
      }
    }
    return $filter_vocabularies;
  }

}
