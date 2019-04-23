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

  /**
   * Get selected bundles from stored config.
   *
   * @param array $form
   *   Form array.
   *
   * @return array
   *   Selected bundles in array.
   */
  public function getSelectedBundles(array $form): array;

  /**
   * Get selectable bundles.
   *
   * @return array
   *   Selectable bundles array, named entity_type:bundle.
   */
  public function getSelectableBundles(): array;

  /**
   * Get selectable bundles.
   *
   * @param string $vid
   *   Vocabulary id.
   * @param array $allowed_entity_types
   *   Allowed entity types array from form_state.
   */
  public function saveAllowedBundlesToVocabulary(string $vid, array $allowed_entity_types): void;

}
