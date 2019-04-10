<?php

namespace Drupal\cp_taxonomy;

/**
 * Helper functions interface.
 */
interface CpTaxonomyHelperInterface {

  /**
   * Read entity_autocomplete ajax call settings array.
   *
   * Read entity_autocomplete ajax call settings array by selection_setting_key
   * from request. This array is store entity type and bundle in bundle_key.
   *
   * @return array
   *   Selection settings.
   */
  public function getTaxonomyTermSettingsFromRequest(): array;

  /**
   * Find out the list of vocabulary vids.
   *
   * Related to current entity type and bundle which is stored in config.
   *
   * @param string $bundle_key
   *   Entity type and bundle information.
   *
   * @return array
   *   List of vocabulary vid.
   */
  public function searchAllowedVocabulariesByType(string $bundle_key): array;

}
