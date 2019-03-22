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
   * Default publications settings.
   *
   * @var array
   */
  protected $defaultPublicationsSettings;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->configFactory = $this->container->get('config.factory');
    $this->repec = $this->container->get('repec');
    $this->defaultPublicationsSettings = $this->configFactory->get('os_publications.settings')->getRawData();

    /** @var \Drupal\Core\Config\Config $publications_settings_mut */
    $publications_settings_mut = $this->configFactory->getEditable('os_publications.settings');
    $publications_settings_mut->set('citation_distribute_module_mode', CitationDistributionModes::BATCH);
    $publications_settings_mut->save();
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
    /** @var \Drupal\Core\Config\Config $publications_settings_mut */
    $publications_settings_mut = $this->configFactory->getEditable('os_publications.settings');
    $publications_settings_mut->setData($this->defaultPublicationsSettings);
    $publications_settings_mut->save(TRUE);

    parent::tearDown();
  }

}
