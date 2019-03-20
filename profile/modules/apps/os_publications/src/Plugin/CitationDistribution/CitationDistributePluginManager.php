<?php

namespace Drupal\os_publications\Plugin\CitationDistribution;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\os_publications\CitationDistributionModes;

/**
 * Class CitationDistributePluginManager.
 */
class CitationDistributePluginManager extends DefaultPluginManager {

  use StringTranslationTrait;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cacheBackend, ModuleHandlerInterface $module_handler, ConfigFactory $config_factory, MessengerInterface $messenger) {
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
    $this->messenger = $messenger;
  }

  /**
   * Distributes the entity in repositories.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   */
  public function distribute(EntityInterface $entity) {
    /** @var string $dist_mode */
    $dist_mode = $this->configFactory->get('citation_distribute.settings')->get('citation_distribute_module_mode');

    try {
      /** @var \Drupal\options\Plugin\Field\FieldType\ListStringItem $item */
      foreach ($entity->get('distribution') as $item) {
        /** @var \Drupal\os_publications\Plugin\CitationDistribution\CitationDistributionInterface $plugin */
        $plugin = $this->createInstance($item->getValue()['value']);

        if ($dist_mode === CitationDistributionModes::PER_SUBMISSION) {
          $plugin->save($entity);
        }
        else {
          // TODO: Implement Queue Api for adding entity for cron operation.
        }
      }
    }
    catch (\Exception $e) {
      $this->messenger->addError($this->t('Could not create citation. Check logs for more information.'));
      $this->loggerFactory->get('os_publications')->error($e->getMessage());
    }
  }

  /**
   * Conceals a citation from repositories.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The citation entity.
   */
  public function conceal(EntityInterface $entity) {
    /** @var string $dist_mode */
    $dist_mode = $this->configFactory->get('citation_distribute.settings')->get('citation_distribute_module_mode');

    try {
      /** @var \Drupal\options\Plugin\Field\FieldType\ListStringItem $item */
      foreach ($entity->get('distribution') as $item) {
        /** @var \Drupal\os_publications\Plugin\CitationDistribution\CitationDistributionInterface $plugin */
        $plugin = $this->createInstance($item->getValue()['value']);

        if ($dist_mode === CitationDistributionModes::PER_SUBMISSION) {
          $plugin->delete($entity);
        }
        else {
          // TODO: Implement Queue Api for adding entity for cron operation.
        }
      }
    }
    catch (\Exception $e) {
      $this->messenger->addError($this->t('Could not delete citation. Check logs for more information.'));
      $this->loggerFactory->get('os_publications')->error($e->getMessage());
    }
  }

}
