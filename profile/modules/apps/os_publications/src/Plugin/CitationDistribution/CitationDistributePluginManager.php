<?php

namespace Drupal\os_publications\Plugin\CitationDistribution;

use Drupal\advancedqueue\Entity\Queue;
use Drupal\advancedqueue\Job;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
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
   * CitationDistributePluginManager constructor.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cacheBackend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   *   Config factory.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   Messenger service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_channel_factory
   *   Logger factory.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cacheBackend, ModuleHandlerInterface $module_handler, ConfigFactory $config_factory, MessengerInterface $messenger, LoggerChannelFactoryInterface $logger_channel_factory) {
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
    $this->loggerFactory = $logger_channel_factory;
  }

  /**
   * Distributes the entity in repositories.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   */
  public function distribute(EntityInterface $entity) {
    /** @var string $dist_mode */
    $dist_mode = $this->configFactory->get('os_publications.settings')->get('citation_distribute_module_mode');

    try {
      /** @var \Drupal\options\Plugin\Field\FieldType\ListStringItem $item */
      foreach ($entity->get('distribution') as $item) {
        /** @var \Drupal\os_publications\Plugin\CitationDistribution\CitationDistributionInterface $plugin */
        $plugin = $this->createInstance($item->getValue()['value']);

        switch ($dist_mode) {
          case CitationDistributionModes::PER_SUBMISSION:
            $plugin->save($entity);

            break;

          case CitationDistributionModes::BATCH:
            $job = Job::create('os_publications_citation_distribute', [
              'id' => $entity->id(),
            ]);
            /** @var \Drupal\advancedqueue\Entity\QueueInterface $queue */
            $queue = Queue::load('publications');
            $queue->enqueueJob($job);

            break;
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
    $dist_mode = $this->configFactory->get('os_publications.settings')->get('citation_distribute_module_mode');

    try {
      /** @var \Drupal\options\Plugin\Field\FieldType\ListStringItem $item */
      foreach ($entity->get('distribution') as $item) {
        /** @var \Drupal\os_publications\Plugin\CitationDistribution\CitationDistributionInterface $plugin */
        $plugin = $this->createInstance($item->getValue()['value']);

        switch ($dist_mode) {
          case CitationDistributionModes::PER_SUBMISSION:
            /** @var \Drupal\os_publications\GhostEntityInterface $ghost_entity */
            $ghost_entity = $plugin->killEntity($entity);
            $plugin->delete($ghost_entity);

            break;

          case CitationDistributionModes::BATCH:
            $job = Job::create('os_publications_citation_conceal', [
              'id' => $entity->id(),
              'type' => $entity->getEntityTypeId(),
              'bundle' => $entity->bundle(),
            ]);
            /** @var \Drupal\advancedqueue\Entity\QueueInterface $queue */
            $queue = Queue::load('publications');
            $queue->enqueueJob($job);

            break;
        }
      }
    }
    catch (\Exception $e) {
      $this->messenger->addError($this->t('Could not delete citation. Check logs for more information.'));
      $this->loggerFactory->get('os_publications')->error($e->getMessage());
    }
  }

}
