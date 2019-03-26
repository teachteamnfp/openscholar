<?php

namespace Drupal\Tests\os_publications\ExistingSite;

use Drupal\advancedqueue\Entity\Queue;
use Drupal\advancedqueue\Job;
use Drupal\Component\Serialization\Json;
use Drupal\os_publications\CitationDistributionModes;

/**
 * CitationDistributionBatchModeTest.
 *
 * @group kernel
 * @group publications
 */
class CitationDistributionBatchModeTest extends TestBase {

  /**
   * Repec service.
   *
   * @var \Drupal\repec\Repec
   */
  protected $repec;

  /**
   * Config service.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * Default publications settings.
   *
   * @var array
   */
  protected $defaultPublicationsSettings;

  /**
   * Database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $databaseConnection;

  /**
   * Queue that would store the jobs.
   *
   * Required for cleanup.
   *
   * @var \Drupal\advancedqueue\Entity\QueueInterface
   */
  protected $queue;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->configFactory = $this->container->get('config.factory');
    $this->repec = $this->container->get('repec');
    $this->defaultPublicationsSettings = $this->configFactory->get('os_publications.settings')->getRawData();
    $this->databaseConnection = $this->container->get('database');
    $this->queue = Queue::load('publications');

    /** @var \Drupal\Core\Config\Config $publications_settings_mut */
    $publications_settings_mut = $this->configFactory->getEditable('os_publications.settings');
    $publications_settings_mut->set('citation_distribute_module_mode', CitationDistributionModes::BATCH);
    $publications_settings_mut->save();
  }

  /**
   * Tests behavior of batch mode for repec.
   *
   * @covers \Drupal\os_publications\Plugin\CitationDistribution\CitationDistributePluginManager::distribute
   * @covers \Drupal\os_publications\Plugin\CitationDistribution\CitationDistributePluginManager::conceal
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testRepec() {
    // Test creation.
    $reference = $this->createReference();
    $serie_directory_config = $this->repec->getEntityBundleSettings('serie_directory', $reference->getEntityTypeId(), $reference->bundle());
    $directory = "{$this->repec->getArchiveDirectory()}{$serie_directory_config}/";
    $file_name = "{$serie_directory_config}_{$reference->getEntityTypeId()}_{$reference->id()}.rdf";

    $this->assertFileNotExists("$directory/$file_name");

    $raw_jobs = $this->databaseConnection->query('SELECT * FROM {advancedqueue} WHERE {queue_id} = :queue_id AND {type} = :type AND {state} = :state', [
      ':queue_id' => 'publications',
      ':type' => 'os_publications_citation_distribute',
      ':state' => Job::STATE_QUEUED,
    ])->fetchAllAssoc('job_id', \PDO::FETCH_ASSOC);

    $this->assertCount(1, $raw_jobs);

    $job = reset($raw_jobs);
    $payload = Json::decode($job['payload']);

    $this->assertEquals($reference->id(), $payload['id']);

    // Test deletion.
    $reference->delete();

    $raw_jobs = $this->databaseConnection->query('SELECT * FROM {advancedqueue} WHERE {queue_id} = :queue_id AND {type} = :type AND {state} = :state', [
      ':queue_id' => 'publications',
      ':type' => 'os_publications_citation_conceal',
      ':state' => Job::STATE_QUEUED,
    ])->fetchAllAssoc('job_id', \PDO::FETCH_ASSOC);

    $this->assertCount(1, $raw_jobs);

    $job = reset($raw_jobs);
    $payload = Json::decode($job['payload']);

    $this->assertEquals($reference->id(), $payload['id']);
  }

  /**
   * {@inheritdoc}
   */
  public function tearDown() {
    /** @var \Drupal\Core\Config\Config $publications_settings_mut */
    $publications_settings_mut = $this->configFactory->getEditable('os_publications.settings');
    $publications_settings_mut->setData($this->defaultPublicationsSettings);
    $publications_settings_mut->save(TRUE);

    parent::tearDown();

    $this->queue->getBackend()->deleteQueue();
  }

}
