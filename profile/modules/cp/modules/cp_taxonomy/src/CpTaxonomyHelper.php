<?php

namespace Drupal\cp_taxonomy;

use Drupal\Core\Config\ConfigFactoryInterface;

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
  public function getTaxonomyTermSettingsFromRequest(): array {
    $selection_settings_key = \Drupal::routeMatch()->getParameter('selection_settings_key');
    $key_value_storage = \Drupal::keyValue('entity_autocomplete');
    if ($key_value_storage->has($selection_settings_key)) {
      return $key_value_storage->get($selection_settings_key);
    }
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function searchAllowedVocabulariesByType(string $bundle_key): array {
    $config_allowed_vocabulary_reference = $this->configFactory->get('cp_taxonomy.settings.allowed_vocabulary_reference_types')->get();
    $vocabularies = [];
    if (!empty($config_allowed_vocabulary_reference)) {
      foreach ($config_allowed_vocabulary_reference as $vid => $bundle_keys) {
        if (in_array($bundle_key, $bundle_keys)) {
          $vocabularies[$vid] = $vid;
        }
      }
    }
    return $vocabularies;
  }

}
