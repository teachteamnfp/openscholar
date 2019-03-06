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
    /** @var array $default_repec_setting */
    $default_repec_setting = $this->configFactory->get('repec.settings')->getRawData();
    unset($default_repec_setting['_core']);
    $this->defaultRepecSettings = $default_repec_setting;

    /** @var \Drupal\Core\Config\Config $repec_settings_mut */
    $repec_settings_mut = $this->configFactory->getEditable('repec.settings');
    $repec_settings_mut->setData([
      'base_path' => getenv('REPEC_BASE_PATH'),
      'archive_code' => getenv('REPEC_ARCHIVE_CODE'),
      'provider_name' => getenv('REPEC_PROVIDER_NAME'),
      'provider_homepage' => getenv('REPEC_PROVIDER_HOMEPAGE'),
      'provider_institution' => getenv('REPEC_PROVIDER_INSTITUTION'),
      'maintainer_name' => getenv('REPEC_MAINTAINER_NAME'),
      'maintainer_email' => getenv('REPEC_MAINTAINER_EMAIL'),
      'repec_bundle' => [
        'bibcite_reference' => [
          'artwork' => 'a:10:{s:7:"enabled";i:1;s:10:"serie_type";s:6:"wpaper";s:10:"serie_name";s:7:"artwork";s:15:"serie_directory";s:6:"wpaper";s:17:"restriction_field";s:9:"is_sticky";s:11:"author_name";s:6:"author";s:8:"abstract";s:14:"bibcite_abst_e";s:13:"creation_date";s:7:"created";s:8:"file_url";s:11:"bibcite_url";s:8:"keywords";s:8:"keywords";}',
          'audiovisual' => 'a:10:{s:7:"enabled";i:1;s:10:"serie_type";s:6:"wpaper";s:10:"serie_name";s:7:"artwork";s:15:"serie_directory";s:6:"wpaper";s:17:"restriction_field";s:9:"is_sticky";s:11:"author_name";s:6:"author";s:8:"abstract";s:14:"bibcite_abst_e";s:13:"creation_date";s:7:"created";s:8:"file_url";s:11:"bibcite_url";s:8:"keywords";s:8:"keywords";}',
          'bill' => 'a:10:{s:7:"enabled";i:1;s:10:"serie_type";s:6:"wpaper";s:10:"serie_name";s:7:"artwork";s:15:"serie_directory";s:6:"wpaper";s:17:"restriction_field";s:9:"is_sticky";s:11:"author_name";s:6:"author";s:8:"abstract";s:14:"bibcite_abst_e";s:13:"creation_date";s:7:"created";s:8:"file_url";s:11:"bibcite_url";s:8:"keywords";s:8:"keywords";}',
          'book' => 'a:10:{s:7:"enabled";i:1;s:10:"serie_type";s:6:"wpaper";s:10:"serie_name";s:7:"artwork";s:15:"serie_directory";s:6:"wpaper";s:17:"restriction_field";s:9:"is_sticky";s:11:"author_name";s:6:"author";s:8:"abstract";s:14:"bibcite_abst_e";s:13:"creation_date";s:7:"created";s:8:"file_url";s:11:"bibcite_url";s:8:"keywords";s:8:"keywords";}',
          'book_chapter' => 'a:10:{s:7:"enabled";i:1;s:10:"serie_type";s:6:"wpaper";s:10:"serie_name";s:7:"artwork";s:15:"serie_directory";s:6:"wpaper";s:17:"restriction_field";s:9:"is_sticky";s:11:"author_name";s:6:"author";s:8:"abstract";s:14:"bibcite_abst_e";s:13:"creation_date";s:7:"created";s:8:"file_url";s:11:"bibcite_url";s:8:"keywords";s:8:"keywords";}',
          'broadcast' => 'a:10:{s:7:"enabled";i:1;s:10:"serie_type";s:6:"wpaper";s:10:"serie_name";s:7:"artwork";s:15:"serie_directory";s:6:"wpaper";s:17:"restriction_field";s:9:"is_sticky";s:11:"author_name";s:6:"author";s:8:"abstract";s:14:"bibcite_abst_e";s:13:"creation_date";s:7:"created";s:8:"file_url";s:11:"bibcite_url";s:8:"keywords";s:8:"keywords";}',
          'case' => 'a:10:{s:7:"enabled";i:1;s:10:"serie_type";s:6:"wpaper";s:10:"serie_name";s:7:"artwork";s:15:"serie_directory";s:6:"wpaper";s:17:"restriction_field";s:9:"is_sticky";s:11:"author_name";s:6:"author";s:8:"abstract";s:14:"bibcite_abst_e";s:13:"creation_date";s:7:"created";s:8:"file_url";s:11:"bibcite_url";s:8:"keywords";s:8:"keywords";}',
          'chart' => 'a:10:{s:7:"enabled";i:1;s:10:"serie_type";s:6:"wpaper";s:10:"serie_name";s:7:"artwork";s:15:"serie_directory";s:6:"wpaper";s:17:"restriction_field";s:9:"is_sticky";s:11:"author_name";s:6:"author";s:8:"abstract";s:14:"bibcite_abst_e";s:13:"creation_date";s:7:"created";s:8:"file_url";s:11:"bibcite_url";s:8:"keywords";s:8:"keywords";}',
          'classical' => 'a:10:{s:7:"enabled";i:1;s:10:"serie_type";s:6:"wpaper";s:10:"serie_name";s:7:"artwork";s:15:"serie_directory";s:6:"wpaper";s:17:"restriction_field";s:9:"is_sticky";s:11:"author_name";s:6:"author";s:8:"abstract";s:14:"bibcite_abst_e";s:13:"creation_date";s:7:"created";s:8:"file_url";s:11:"bibcite_url";s:8:"keywords";s:8:"keywords";}',
          'conference_paper' => 'a:10:{s:7:"enabled";i:1;s:10:"serie_type";s:6:"wpaper";s:10:"serie_name";s:7:"artwork";s:15:"serie_directory";s:6:"wpaper";s:17:"restriction_field";s:9:"is_sticky";s:11:"author_name";s:6:"author";s:8:"abstract";s:14:"bibcite_abst_e";s:13:"creation_date";s:7:"created";s:8:"file_url";s:11:"bibcite_url";s:8:"keywords";s:8:"keywords";}',
          'conference_proceedings' => 'a:10:{s:7:"enabled";i:1;s:10:"serie_type";s:6:"wpaper";s:10:"serie_name";s:7:"artwork";s:15:"serie_directory";s:6:"wpaper";s:17:"restriction_field";s:9:"is_sticky";s:11:"author_name";s:6:"author";s:8:"abstract";s:14:"bibcite_abst_e";s:13:"creation_date";s:7:"created";s:8:"file_url";s:11:"bibcite_url";s:8:"keywords";s:8:"keywords";}',
          'database' => 'a:10:{s:7:"enabled";i:1;s:10:"serie_type";s:6:"wpaper";s:10:"serie_name";s:7:"artwork";s:15:"serie_directory";s:6:"wpaper";s:17:"restriction_field";s:9:"is_sticky";s:11:"author_name";s:6:"author";s:8:"abstract";s:14:"bibcite_abst_e";s:13:"creation_date";s:7:"created";s:8:"file_url";s:11:"bibcite_url";s:8:"keywords";s:8:"keywords";}',
          'film' => 'a:10:{s:7:"enabled";i:1;s:10:"serie_type";s:6:"wpaper";s:10:"serie_name";s:7:"artwork";s:15:"serie_directory";s:6:"wpaper";s:17:"restriction_field";s:9:"is_sticky";s:11:"author_name";s:6:"author";s:8:"abstract";s:14:"bibcite_abst_e";s:13:"creation_date";s:7:"created";s:8:"file_url";s:11:"bibcite_url";s:8:"keywords";s:8:"keywords";}',
          'government_report' => 'a:10:{s:7:"enabled";i:1;s:10:"serie_type";s:6:"wpaper";s:10:"serie_name";s:7:"artwork";s:15:"serie_directory";s:6:"wpaper";s:17:"restriction_field";s:9:"is_sticky";s:11:"author_name";s:6:"author";s:8:"abstract";s:14:"bibcite_abst_e";s:13:"creation_date";s:7:"created";s:8:"file_url";s:11:"bibcite_url";s:8:"keywords";s:8:"keywords";}',
          'hearing' => 'a:10:{s:7:"enabled";i:1;s:10:"serie_type";s:6:"wpaper";s:10:"serie_name";s:7:"artwork";s:15:"serie_directory";s:6:"wpaper";s:17:"restriction_field";s:9:"is_sticky";s:11:"author_name";s:6:"author";s:8:"abstract";s:14:"bibcite_abst_e";s:13:"creation_date";s:7:"created";s:8:"file_url";s:11:"bibcite_url";s:8:"keywords";s:8:"keywords";}',
          'journal' => 'a:10:{s:7:"enabled";i:1;s:10:"serie_type";s:6:"wpaper";s:10:"serie_name";s:7:"artwork";s:15:"serie_directory";s:6:"wpaper";s:17:"restriction_field";s:9:"is_sticky";s:11:"author_name";s:6:"author";s:8:"abstract";s:14:"bibcite_abst_e";s:13:"creation_date";s:7:"created";s:8:"file_url";s:11:"bibcite_url";s:8:"keywords";s:8:"keywords";}',
          'journal_article' => 'a:10:{s:7:"enabled";i:1;s:10:"serie_type";s:6:"wpaper";s:10:"serie_name";s:7:"artwork";s:15:"serie_directory";s:6:"wpaper";s:17:"restriction_field";s:9:"is_sticky";s:11:"author_name";s:6:"author";s:8:"abstract";s:14:"bibcite_abst_e";s:13:"creation_date";s:7:"created";s:8:"file_url";s:11:"bibcite_url";s:8:"keywords";s:8:"keywords";}',
          'legal_ruling' => 'a:10:{s:7:"enabled";i:1;s:10:"serie_type";s:6:"wpaper";s:10:"serie_name";s:7:"artwork";s:15:"serie_directory";s:6:"wpaper";s:17:"restriction_field";s:9:"is_sticky";s:11:"author_name";s:6:"author";s:8:"abstract";s:14:"bibcite_abst_e";s:13:"creation_date";s:7:"created";s:8:"file_url";s:11:"bibcite_url";s:8:"keywords";s:8:"keywords";}',
          'magazine_article' => 'a:10:{s:7:"enabled";i:1;s:10:"serie_type";s:6:"wpaper";s:10:"serie_name";s:7:"artwork";s:15:"serie_directory";s:6:"wpaper";s:17:"restriction_field";s:9:"is_sticky";s:11:"author_name";s:6:"author";s:8:"abstract";s:14:"bibcite_abst_e";s:13:"creation_date";s:7:"created";s:8:"file_url";s:11:"bibcite_url";s:8:"keywords";s:8:"keywords";}',
          'manuscript' => 'a:10:{s:7:"enabled";i:1;s:10:"serie_type";s:6:"wpaper";s:10:"serie_name";s:7:"artwork";s:15:"serie_directory";s:6:"wpaper";s:17:"restriction_field";s:9:"is_sticky";s:11:"author_name";s:6:"author";s:8:"abstract";s:14:"bibcite_abst_e";s:13:"creation_date";s:7:"created";s:8:"file_url";s:11:"bibcite_url";s:8:"keywords";s:8:"keywords";}',
          'map' => 'a:10:{s:7:"enabled";i:1;s:10:"serie_type";s:6:"wpaper";s:10:"serie_name";s:7:"artwork";s:15:"serie_directory";s:6:"wpaper";s:17:"restriction_field";s:9:"is_sticky";s:11:"author_name";s:6:"author";s:8:"abstract";s:14:"bibcite_abst_e";s:13:"creation_date";s:7:"created";s:8:"file_url";s:11:"bibcite_url";s:8:"keywords";s:8:"keywords";}',
          'miscellaneous' => 'a:10:{s:7:"enabled";i:1;s:10:"serie_type";s:6:"wpaper";s:10:"serie_name";s:7:"artwork";s:15:"serie_directory";s:6:"wpaper";s:17:"restriction_field";s:9:"is_sticky";s:11:"author_name";s:6:"author";s:8:"abstract";s:14:"bibcite_abst_e";s:13:"creation_date";s:7:"created";s:8:"file_url";s:11:"bibcite_url";s:8:"keywords";s:8:"keywords";}',
          'miscellaneous_section' => 'a:10:{s:7:"enabled";i:1;s:10:"serie_type";s:6:"wpaper";s:10:"serie_name";s:7:"artwork";s:15:"serie_directory";s:6:"wpaper";s:17:"restriction_field";s:9:"is_sticky";s:11:"author_name";s:6:"author";s:8:"abstract";s:14:"bibcite_abst_e";s:13:"creation_date";s:7:"created";s:8:"file_url";s:11:"bibcite_url";s:8:"keywords";s:8:"keywords";}',
          'newspaper_article' => 'a:10:{s:7:"enabled";i:1;s:10:"serie_type";s:6:"wpaper";s:10:"serie_name";s:7:"artwork";s:15:"serie_directory";s:6:"wpaper";s:17:"restriction_field";s:9:"is_sticky";s:11:"author_name";s:6:"author";s:8:"abstract";s:14:"bibcite_abst_e";s:13:"creation_date";s:7:"created";s:8:"file_url";s:11:"bibcite_url";s:8:"keywords";s:8:"keywords";}',
          'patent' => 'a:10:{s:7:"enabled";i:1;s:10:"serie_type";s:6:"wpaper";s:10:"serie_name";s:7:"artwork";s:15:"serie_directory";s:6:"wpaper";s:17:"restriction_field";s:9:"is_sticky";s:11:"author_name";s:6:"author";s:8:"abstract";s:14:"bibcite_abst_e";s:13:"creation_date";s:7:"created";s:8:"file_url";s:11:"bibcite_url";s:8:"keywords";s:8:"keywords";}',
          'personal' => 'a:10:{s:7:"enabled";i:1;s:10:"serie_type";s:6:"wpaper";s:10:"serie_name";s:7:"artwork";s:15:"serie_directory";s:6:"wpaper";s:17:"restriction_field";s:9:"is_sticky";s:11:"author_name";s:6:"author";s:8:"abstract";s:14:"bibcite_abst_e";s:13:"creation_date";s:7:"created";s:8:"file_url";s:11:"bibcite_url";s:8:"keywords";s:8:"keywords";}',
          'presentation' => 'a:10:{s:7:"enabled";i:1;s:10:"serie_type";s:6:"wpaper";s:10:"serie_name";s:7:"artwork";s:15:"serie_directory";s:6:"wpaper";s:17:"restriction_field";s:9:"is_sticky";s:11:"author_name";s:6:"author";s:8:"abstract";s:14:"bibcite_abst_e";s:13:"creation_date";s:7:"created";s:8:"file_url";s:11:"bibcite_url";s:8:"keywords";s:8:"keywords";}',
          'report' => 'a:10:{s:7:"enabled";i:1;s:10:"serie_type";s:6:"wpaper";s:10:"serie_name";s:7:"artwork";s:15:"serie_directory";s:6:"wpaper";s:17:"restriction_field";s:9:"is_sticky";s:11:"author_name";s:6:"author";s:8:"abstract";s:14:"bibcite_abst_e";s:13:"creation_date";s:7:"created";s:8:"file_url";s:11:"bibcite_url";s:8:"keywords";s:8:"keywords";}',
          'software' => 'a:10:{s:7:"enabled";i:1;s:10:"serie_type";s:6:"wpaper";s:10:"serie_name";s:7:"artwork";s:15:"serie_directory";s:6:"wpaper";s:17:"restriction_field";s:9:"is_sticky";s:11:"author_name";s:6:"author";s:8:"abstract";s:14:"bibcite_abst_e";s:13:"creation_date";s:7:"created";s:8:"file_url";s:11:"bibcite_url";s:8:"keywords";s:8:"keywords";}',
          'statute' => 'a:10:{s:7:"enabled";i:1;s:10:"serie_type";s:6:"wpaper";s:10:"serie_name";s:7:"artwork";s:15:"serie_directory";s:6:"wpaper";s:17:"restriction_field";s:9:"is_sticky";s:11:"author_name";s:6:"author";s:8:"abstract";s:14:"bibcite_abst_e";s:13:"creation_date";s:7:"created";s:8:"file_url";s:11:"bibcite_url";s:8:"keywords";s:8:"keywords";}',
          'thesis' => 'a:10:{s:7:"enabled";i:1;s:10:"serie_type";s:6:"wpaper";s:10:"serie_name";s:7:"artwork";s:15:"serie_directory";s:6:"wpaper";s:17:"restriction_field";s:9:"is_sticky";s:11:"author_name";s:6:"author";s:8:"abstract";s:14:"bibcite_abst_e";s:13:"creation_date";s:7:"created";s:8:"file_url";s:11:"bibcite_url";s:8:"keywords";s:8:"keywords";}',
          'unpublished' => 'a:10:{s:7:"enabled";i:1;s:10:"serie_type";s:6:"wpaper";s:10:"serie_name";s:7:"artwork";s:15:"serie_directory";s:6:"wpaper";s:17:"restriction_field";s:9:"is_sticky";s:11:"author_name";s:6:"author";s:8:"abstract";s:14:"bibcite_abst_e";s:13:"creation_date";s:7:"created";s:8:"file_url";s:11:"bibcite_url";s:8:"keywords";s:8:"keywords";}',
          'web_article' => 'a:10:{s:7:"enabled";i:1;s:10:"serie_type";s:6:"wpaper";s:10:"serie_name";s:7:"artwork";s:15:"serie_directory";s:6:"wpaper";s:17:"restriction_field";s:9:"is_sticky";s:11:"author_name";s:6:"author";s:8:"abstract";s:14:"bibcite_abst_e";s:13:"creation_date";s:7:"created";s:8:"file_url";s:11:"bibcite_url";s:8:"keywords";s:8:"keywords";}',
          'web_project_page' => 'a:10:{s:7:"enabled";i:1;s:10:"serie_type";s:6:"wpaper";s:10:"serie_name";s:7:"artwork";s:15:"serie_directory";s:6:"wpaper";s:17:"restriction_field";s:9:"is_sticky";s:11:"author_name";s:6:"author";s:8:"abstract";s:14:"bibcite_abst_e";s:13:"creation_date";s:7:"created";s:8:"file_url";s:11:"bibcite_url";s:8:"keywords";s:8:"keywords";}',
          'web_service' => 'a:10:{s:7:"enabled";i:1;s:10:"serie_type";s:6:"wpaper";s:10:"serie_name";s:7:"artwork";s:15:"serie_directory";s:6:"wpaper";s:17:"restriction_field";s:9:"is_sticky";s:11:"author_name";s:6:"author";s:8:"abstract";s:14:"bibcite_abst_e";s:13:"creation_date";s:7:"created";s:8:"file_url";s:11:"bibcite_url";s:8:"keywords";s:8:"keywords";}',
          'website' => 'a:10:{s:7:"enabled";i:1;s:10:"serie_type";s:6:"wpaper";s:10:"serie_name";s:7:"artwork";s:15:"serie_directory";s:6:"wpaper";s:17:"restriction_field";s:9:"is_sticky";s:11:"author_name";s:6:"author";s:8:"abstract";s:14:"bibcite_abst_e";s:13:"creation_date";s:7:"created";s:8:"file_url";s:11:"bibcite_url";s:8:"keywords";s:8:"keywords";}',
        ],
      ],
    ]);
    $repec_settings_mut->save(TRUE);
  }

  /**
   * Tests repec integration for reference entity.
   *
   * @covers ::repec_entity_insert
   * @covers ::repec_entity_update
   * @covers ::repec_entity_delete
   * @covers \Drupal\repec\Form\EntityTypeSettingsForm
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
