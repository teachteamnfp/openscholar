<?php

namespace Drupal\Tests\os_publications\ExistingSite;

use Drupal\os_publications\CitationDistributionModes;

/**
 * CitationDistributionBatchModeTest.
 *
 * @group kernel
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
   * Default citation distribution settings.
   *
   * @var array
   */
  protected $defaultCitationDistributionSettings;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->configFactory = $this->container->get('config.factory');
    $this->repec = $this->container->get('repec');
    $this->defaultCitationDistributionSettings = $this->configFactory->get('citation_distribute.settings')->getRawData();

    /** @var \Drupal\Core\Config\Config $citation_distribution_settings_mut */
    $citation_distribution_settings_mut = $this->configFactory->getEditable('citation_distribute.settings');
    $citation_distribution_settings_mut->set('citation_distribute_module_mode', CitationDistributionModes::BATCH);
    $citation_distribution_settings_mut->save();
  }

  /**
   * Tests behavior of batch mode for repec.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testRepec() {
    $reference = $this->createReference();
    $serie_directory_config = $this->repec->getEntityBundleSettings('serie_directory', $reference->getEntityTypeId(), $reference->bundle());
    $directory = "{$this->repec->getArchiveDirectory()}{$serie_directory_config}/";
    $file_name = "{$serie_directory_config}_{$reference->getEntityTypeId()}_{$reference->id()}.rdf";

    $this->assertFileNotExists("$directory/$file_name");
  }

  /**
   * {@inheritdoc}
   */
  public function tearDown() {
    /** @var \Drupal\Core\Config\Config $citation_distribution_settings_mut */
    $citation_distribution_settings_mut = $this->configFactory->getEditable('citation_distribute.settings');
    $citation_distribution_settings_mut->setData($this->defaultCitationDistributionSettings);
    $citation_distribution_settings_mut->save(TRUE);

    parent::tearDown();
  }

}
