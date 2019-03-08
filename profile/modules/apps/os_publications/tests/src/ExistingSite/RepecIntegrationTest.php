<?php

namespace Drupal\Tests\os_publications\ExistingSite;

/**
 * RepecIntegrationTest.
 *
 * @group kernel
 */
class RepecIntegrationTest extends TestBase {

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
   * Default repec settings.
   *
   * @var array
   */
  protected $defaultRepecSettings;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->repec = $this->container->get('repec');
    $this->configFactory = $this->container->get('config.factory');
    $this->defaultRepecSettings = $this->configFactory->get('repec.settings')->getRawData();
  }

  /**
   * Tests repec integration for reference entity.
   *
   * @covers ::repec_entity_insert
   * @covers ::repec_entity_update
   * @covers ::repec_entity_delete
   * @covers \Drupal\repec\Form\EntityTypeSettingsForm
   * @covers \Drupal\repec\Series\Base::create
   * @covers \Drupal\repec\Series\Base::getDefault
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testReference() {
    $reference = $this->createReference();
    $serie_directory_config = $this->repec->getEntityBundleSettings('serie_directory', $reference->getEntityTypeId(), $reference->bundle());
    $directory = "{$this->repec->getArchiveDirectory()}{$serie_directory_config}/";
    $file_name = "{$serie_directory_config}_{$reference->getEntityTypeId()}_{$reference->id()}.rdf";
    $this->assertFileExists("$directory/$file_name");

    $reference->save();
    $this->assertFileExists("$directory/$file_name");

    $content = file_get_contents("$directory/$file_name");
    $this->assertContains("Title: {$reference->label()}", $content);
    $this->assertContains("Number: {$reference->uuid()}", $content);
    $this->assertContains("Handle: RePEc:{$this->defaultRepecSettings['archive_code']}:{$this->repec->getEntityBundleSettings('serie_type', $reference->getEntityTypeId(), $reference->bundle())}:{$reference->id()}", $content);

    $reference->delete();
    $this->assertFileNotExists("$directory/$file_name");
  }

  /**
   * Tests entity shareable setting for repec.
   *
   * @covers \Drupal\repec\Repec::isEntityShareable
   * @covers \Drupal\repec\Repec::createEntityTemplate
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testEntityShareable() {
    $reference = $this->createReference();
    $serie_directory_config = $this->repec->getEntityBundleSettings('serie_directory', $reference->getEntityTypeId(), $reference->bundle());
    $directory = "{$this->repec->getArchiveDirectory()}{$serie_directory_config}/";
    $file_name = "{$serie_directory_config}_{$reference->getEntityTypeId()}_{$reference->id()}.rdf";
    $this->assertFileExists("$directory/$file_name");

    /** @var \Drupal\Core\Config\Config $repec_settings_mut */
    $repec_settings_mut = $this->configFactory->getEditable('repec.settings');
    /** @var array $repec_bundle_settings */
    $repec_bundle_settings = $repec_settings_mut->get('repec_bundle');
    $repec_bundle_settings['bibcite_reference']['artwork'] = 'a:11:{s:7:"enabled";i:1;s:10:"serie_type";s:6:"wpaper";s:10:"serie_name";s:7:"artwork";s:15:"serie_directory";s:6:"wpaper";s:17:"restriction_field";s:9:"is_sticky";s:11:"author_name";s:6:"author";s:8:"abstract";s:14:"bibcite_abst_e";s:13:"creation_date";s:7:"created";s:8:"file_url";s:11:"bibcite_url";s:8:"keywords";s:8:"keywords";s:20:"restriction_by_field";i:1;}';
    $repec_settings_mut->set('repec_bundle', $repec_bundle_settings);
    $repec_settings_mut->save();

    $reference = $this->createReference([
      'is_sticky' => [
        'value' => FALSE,
      ],
    ]);
    $file_name = "{$serie_directory_config}_{$reference->getEntityTypeId()}_{$reference->id()}.rdf";
    $this->assertFileNotExists("$directory/$file_name");
  }

  /**
   * {@inheritdoc}
   */
  public function tearDown() {
    /** @var \Drupal\Core\Config\Config $repec_settings_mut */
    $repec_settings_mut = $this->configFactory->getEditable('repec.settings');
    $repec_settings_mut->setData($this->defaultRepecSettings);
    $repec_settings_mut->save(TRUE);

    parent::tearDown();
  }

}
