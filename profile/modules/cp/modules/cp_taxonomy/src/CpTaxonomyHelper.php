<?php

namespace Drupal\cp_taxonomy;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\cp_taxonomy\Plugin\Field\FieldWidget\TaxonomyTermsWidget;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\vsite\Plugin\VsiteContextManagerInterface;

/**
 * Helper functions to handle vocabularies and related entities.
 */
class CpTaxonomyHelper implements CpTaxonomyHelperInterface {

  use StringTranslationTrait;

  private $configFactory;
  private $entityTypeManager;
  private $entityTypeBundleInfo;
  private $vsiteContextManager;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   Entity Type Bundle Info Interface.
   * @param \Drupal\vsite\Plugin\VsiteContextManagerInterface $vsite_context_manager
   *   Vsite context manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager, EntityTypeBundleInfoInterface $entity_type_bundle_info, VsiteContextManagerInterface $vsite_context_manager) {
    $this->configFactory = $config_factory;
    $this->entityTypeManager = $entity_type_manager;
    $this->entityTypeBundleInfo = $entity_type_bundle_info;
    $this->vsiteContextManager = $vsite_context_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function searchAllowedVocabulariesByType(string $bundle_key): array {
    $vsite_vocabularies = Vocabulary::loadMultiple();
    $found_vocabularies = [];
    foreach ($vsite_vocabularies as $vid => $vocabulary) {
      $config_vocab = $this->configFactory->getEditable('taxonomy.vocabulary.' . $vid);
      $bundle_keys = $config_vocab->get('allowed_vocabulary_reference_types');
      if (empty($bundle_keys)) {
        continue;
      }
      if (in_array($bundle_key, $bundle_keys)) {
        $found_vocabularies[$vid] = $vid;
      }
    }
    return $found_vocabularies;
  }

  /**
   * {@inheritdoc}
   */
  public function getVocabularySettings(string $vid): array {
    $settings = [];
    $config_vocab = $this->configFactory->getEditable('taxonomy.vocabulary.' . $vid);
    if (!empty($config_vocab)) {
      $settings['allowed_vocabulary_reference_types'] = $config_vocab->get('allowed_vocabulary_reference_types');
      $widget_type = $config_vocab->get('widget_type');
      $settings['widget_type'] = is_null($widget_type) ? TaxonomyTermsWidget::WIDGET_TYPE_AUTOCOMPLETE : $widget_type;
    }
    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function getSelectableBundles(): array {
    $options = [];
    $options['media:*'] = $this->t('Media');
    $options['bibcite_reference:*'] = $this->t('Publications');
    $definition = $this->entityTypeManager->getDefinition('node');
    $bundles = $this->entityTypeBundleInfo->getBundleInfo($definition->id());
    foreach ($bundles as $machine_name => $bundle) {
      $options[$definition->id() . ':' . $machine_name] = $bundle['label'];
    }
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function saveVocabularySettings(string $vid, array $settings): void {
    $config_vocab = $this->configFactory->getEditable('taxonomy.vocabulary.' . $vid);
    if (!is_null($settings['allowed_entity_types'])) {
      $filtered_entity_types = array_values(array_filter($settings['allowed_entity_types']));
      $config_vocab->set('allowed_vocabulary_reference_types', $filtered_entity_types);
    }
    $config_vocab->set('widget_type', $settings['widget_type']);
    $config_vocab->save(TRUE);
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

  /**
   * {@inheritdoc}
   */
  public function checkTaxonomyTermsPageVisibility(array &$build, array $view_modes): void {
    $config = $this->configFactory->get('cp_taxonomy.settings');
    $display_term_under_content = $config->get('display_term_under_content');
    if (empty($display_term_under_content) && in_array($build['#view_mode'], $view_modes)) {
      $build['field_taxonomy_terms']['#access'] = FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function checkTaxonomyTermsListingVisibility(array &$build, string $entity_type): void {
    $config = $this->configFactory->get('cp_taxonomy.settings');
    $display_term_under_content_teaser_types = $config->get('display_term_under_content_teaser_types');
    // Unset field_taxonomy_terms for unchecked bundles from settings page.
    if (is_array($display_term_under_content_teaser_types) && !in_array($entity_type, $display_term_under_content_teaser_types) && $build['#view_mode'] == 'teaser') {
      $build['field_taxonomy_terms']['#access'] = FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setCacheTags(array &$build): void {
    $group = $this->vsiteContextManager->getActiveVsite();
    if (empty($group)) {
      return;
    }
    if (empty($build['field_taxonomy_terms'])) {
      return;
    }
    $build['#cache']['tags'][] = 'entity-with-taxonomy-terms:' . $group->id();
  }

  /**
   * {@inheritdoc}
   */
  public function getWidgetType(string $entity_bundle): string {
    $vocabularies = $this->searchAllowedVocabulariesByType($entity_bundle);
    foreach ($vocabularies as $vid) {
      $config_vocab = $this->configFactory->get('taxonomy.vocabulary.' . $vid);
      if (empty($config_vocab)) {
        continue;
      }
      $widget_type = $config_vocab->get('widget_type');
      if (empty($widget_type)) {
        continue;
      }
      return $widget_type;
    }
    return TaxonomyTermsWidget::WIDGET_TYPE_AUTOCOMPLETE;
  }

}
