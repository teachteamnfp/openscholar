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
   * Get vocabulary settings from stored config.
   *
   * @param string $vid
   *   Vocabulary id.
   *
   * @return array
   *   Vocabulary settings in array.
   */
  public function getVocabularySettings(string $vid): array;

  /**
   * Get selectable bundles.
   *
   * @return array
   *   Selectable bundles array, named entity_type:bundle.
   */
  public function getSelectableBundles(): array;

  /**
   * Save vocabulary settings.
   *
   * @param string $vid
   *   Vocabulary id.
   * @param array $settings
   *   Settings array.
   */
  public function saveVocabularySettings(string $vid, array $settings): void;

  /**
   * Explode entity bundles.
   *
   * @param array $bundles
   *   Array of entity bundles.
   *
   * @return array
   *   Exploded array, keyed entity name and values are bundles array.
   */
  public function explodeEntityBundles(array $bundles): array;

  /**
   * Check visibility of taxonomy terms on page.
   *
   * @param array $build
   *   View alter build array.
   * @param array $view_modes
   *   Applied view modes.
   */
  public function checkTaxonomyTermsPageVisibility(array &$build, array $view_modes): void;

  /**
   * Check visibility of taxonomy terms on list page.
   *
   * @param array $build
   *   View alter build array.
   * @param string $entity_type
   *   Current entity type with bundle (ex node:news).
   */
  public function checkTaxonomyTermsListingVisibility(array &$build, string $entity_type): void;

  /**
   * Set build cache tags.
   *
   * @param array $build
   *   View alter build array.
   */
  public function setCacheTags(array &$build): void;

  /**
   * Get widget type.
   *
   * @param string $entity_bundle
   *   Entity bundle.
   *
   * @return array
   *   All types of vocabularies related to entity type.
   */
  public function getWidgetTypes(string $entity_bundle): array;

}
