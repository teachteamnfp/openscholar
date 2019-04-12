<?php

namespace Drupal\cp_taxonomy;

/**
 * Helper functions interface.
 */
interface CpTaxonomyHelperInterface {

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
