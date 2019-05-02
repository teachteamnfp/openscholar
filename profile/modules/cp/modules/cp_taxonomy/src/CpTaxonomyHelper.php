<?php

namespace Drupal\cp_taxonomy;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\taxonomy\Entity\Vocabulary;

/**
 * Helper functions to handle vocabularies and related entities.
 */
class CpTaxonomyHelper implements CpTaxonomyHelperInterface {

  use StringTranslationTrait;

  private $configFactory;
  private $entityTypeManager;
  private $entityTypeBundleInfo;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   Entity Type Bundle Info Interface.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager, EntityTypeBundleInfoInterface $entity_type_bundle_info) {
    $this->configFactory = $config_factory;
    $this->entityTypeManager = $entity_type_manager;
    $this->entityTypeBundleInfo = $entity_type_bundle_info;
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

  /**
   * {@inheritdoc}
   */
  public function getSelectedBundles(array $form): array {
    $saved_entity_types = [];
    if (!empty($form['vid']['#default_value'])) {
      $vid = $form['vid']['#default_value'];
      $config_vocab = $this->configFactory->getEditable('taxonomy.vocabulary.' . $vid);
      $config_allowed_vocabulary_reference = $config_vocab->get('allowed_vocabulary_reference_types');
      if (!empty($config_allowed_vocabulary_reference)) {
        $saved_entity_types = $config_allowed_vocabulary_reference;
      }
    }
    return $saved_entity_types;
  }

  /**
   * {@inheritdoc}
   */
  public function getSelectableBundles(): array {
    $definitions = $this->entityTypeManager->getDefinitions();
    $allowed_entity_types = [
      'node',
      'media',
      'bibcite_reference',
    ];
    $options = [];
    foreach ($definitions as $definition) {
      if (!in_array($definition->id(), $allowed_entity_types)) {
        continue;
      }
      $bundles = $this->entityTypeBundleInfo->getBundleInfo($definition->id());
      foreach ($bundles as $machine_name => $bundle) {
        $options[$definition->id() . ':' . $machine_name] = $definition->getLabel() . ' - ' . $bundle['label'];
        if ($definition->id() == 'node' && $machine_name == 'events') {
          $options['node:past_events'] = $definition->getLabel() . ' - ' . $this->t('Past events');
          $options['node:upcoming_events'] = $definition->getLabel() . ' - ' . $this->t('Upcoming events');
        }
      }
    }
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function saveAllowedBundlesToVocabulary(string $vid, array $allowed_entity_types): void {
    $filtered_entity_types = array_values(array_filter($allowed_entity_types));
    $config_vocab = $this->configFactory->getEditable('taxonomy.vocabulary.' . $vid);
    $config_vocab
      ->set('allowed_vocabulary_reference_types', $filtered_entity_types)
      ->save(TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function explodeEntityBundles(array $bundles): array {
    $entities = [];
    foreach ($bundles as $bundle) {
      list($entity_name, $bundle) = explode(':', $bundle);
      $entities[$entity_name][] = $bundle;
    }
    return $entities;
  }

}
