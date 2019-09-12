<?php

namespace Drupal\cp_taxonomy\Plugin\views\argument;

use Drupal\cp_taxonomy\CpTaxonomyHelperInterface;
use Drupal\taxonomy\Plugin\views\argument\VocabularyVid;
use Drupal\taxonomy\VocabularyStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Argument handler to accept entity bundle and convert to vocabulary ids.
 *
 * @ingroup views_argument_handlers
 *
 * @ViewsArgument("entity_bundle_to_vocabulary_vid")
 */
class EntityBundleToVocabularyVid extends VocabularyVid {

  private $cpTaxonomyHelper;

  /**
   * Constructs the EntityBundleToVocabularyVid object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\taxonomy\VocabularyStorageInterface $vocabulary_storage
   *   The vocabulary storage.
   * @param \Drupal\cp_taxonomy\CpTaxonomyHelperInterface $cp_taxonomy_helper
   *   Cp taxonomy helper.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, VocabularyStorageInterface $vocabulary_storage, CpTaxonomyHelperInterface $cp_taxonomy_helper) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $vocabulary_storage);
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
      $container->get('entity.manager')->getStorage('taxonomy_vocabulary'),
      $container->get('cp.taxonomy.helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function query($group_by = FALSE) {
    $parts = explode('|', $this->argument);
    if (!empty($parts[1])) {
      $this->argument = $parts[1];
    }
    else {
      $vocabularies = $this->cpTaxonomyHelper->searchAllowedVocabulariesByType($parts[0]);
      $this->argument = implode(',', array_values($vocabularies));
    }
    parent::query($group_by);
  }

}
