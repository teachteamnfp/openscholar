<?php

namespace Drupal\os_publications\Plugin\CitationDistribution;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Class CitationDistributePluginManager.
 */
class CitationDistributePluginManager extends DefaultPluginManager {

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cacheBackend, ModuleHandlerInterface $module_handler, ConfigFactory $config_factory) {
    parent::__construct(
      'Plugin/CitationDistribution',
      $namespaces,
      $module_handler,
      'Drupal\os_publications\Plugin\CitationDistribution\CitationDistributionInterface',
      'Drupal\os_publications\Annotation\CitationDistribute'
    );

    $this->alterInfo('citation_distribute');
    $this->setCacheBackend($cacheBackend, 'citation_distribute_plugins');
    $this->configFactory = $config_factory;
  }

  /**
   * Distributes the entity in repositories.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   */
  public function distribute(EntityInterface $entity) {
    /** @var \Drupal\options\Plugin\Field\FieldType\ListStringItem $item */
    foreach ($entity->get('distribution') as $item) {
      $dist = FALSE;
      $dist_mode = $this->configFactory->get('citation_distribute.settings')->get('citation_distribute_module_mode');
      $item->getValue()['value'];

      // TODO make sure entity is published first.
      if ($dist_mode == 'per_submission') {
        // TODO: Implement.
      }
      else {
        // Assume batch mode.
        $dist = TRUE;
      }

      if ($dist) {
        // TODO Implement Queue Api for adding entity for cron operation.
      }
    }
  }

}
