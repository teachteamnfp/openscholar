<?php

namespace Drupal\os_publications\Plugin\AdvancedQueue\JobType;

use Drupal\advancedqueue\Job;
use Drupal\advancedqueue\JobResult;
use Drupal\advancedqueue\Plugin\AdvancedQueue\JobType\JobTypeBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\os_publications\Plugin\CitationDistribution\CitationDistributePluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The job type for concealing citations.
 *
 * @AdvancedQueueJobType(
 *   id = "os_publications_citation_conceal",
 *   label = @Translation("Citation Conceal"),
 *   max_retries = 10,
 *   retry_delay = 1,
 * )
 */
class CitationConceal extends JobTypeBase implements ContainerFactoryPluginInterface {

  /**
   * Citation distribution plugin manager.
   *
   * @var \Drupal\os_publications\Plugin\CitationDistribution\CitationDistributePluginManager
   */
  protected $citationDistributionPluginManager;

  /**
   * CitationConceal constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\os_publications\Plugin\CitationDistribution\CitationDistributePluginManager $citation_distribute_plugin_manager
   *   Citation distribution plugin manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, CitationDistributePluginManager $citation_distribute_plugin_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->citationDistributionPluginManager = $citation_distribute_plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('os_publications.manager_citation_distribute'));
  }

  /**
   * {@inheritdoc}
   */
  public function process(Job $job) {
    try {
      /** @var array $payload */
      $payload = $job->getPayload();
      /** @var array $plugin_definitions */
      $plugin_definitions = $this->citationDistributionPluginManager->getDefinitions();

      foreach ($plugin_definitions as $plugin_definition) {
        /** @var \Drupal\os_publications\Plugin\CitationDistribution\CitationDistributionInterface $distribution */
        $distribution = $this->citationDistributionPluginManager->createInstance($plugin_definition['id']);
        /** @var \Drupal\os_publications\GhostEntityInterface $ghost_entity */
        $ghost_entity = $distribution->createGhostEntityFromPayload($payload);
        $distribution->delete($ghost_entity);
      }
    }
    catch (\Exception $e) {
      // The distribution plugin is responsible to handle any exceptions.
      // We just move on.
    }

    return JobResult::success();
  }

}
