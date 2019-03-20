<?php

namespace Drupal\Tests\os_publications\ExistingSite;

use Drupal\bibcite_entity\Entity\Contributor;
use Drupal\bibcite_entity\Entity\Keyword;
use Drupal\bibcite_entity\Entity\ReferenceInterface;
use Drupal\file\Entity\File;

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

    $this->repec = $this->container->get('repec');
    $this->configFactory = $this->container->get('config.factory');
    $this->defaultRepecSettings = $this->configFactory->get('repec.settings')->getRawData();
    $this->defaultCitationDistributionSettings = $this->configFactory->get('citation_distribute.settings')->getRawData();

    $this->changeCitationDistributionMode('per_submission');
  }

  /**
   * Tests repec integration for reference entity.
   *
   * @covers \Drupal\repec\Form\EntityTypeSettingsForm
   * @covers \Drupal\repec\Series\Base::create
   * @covers \Drupal\repec\Series\Base::getDefault
   * @covers \Drupal\os_publications\Plugin\CitationDistribution\CitationDistributeRepec::save
   * @covers \Drupal\os_publications\Plugin\CitationDistribution\CitationDistributeRepec::delete
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testReference() {
    $reference = $this->createReference();
    $serie_directory_config = $this->repec->getEntityBundleSettings('serie_directory', $reference->getEntityTypeId(), $reference->bundle());
    $directory = "{$this->repec->getArchiveDirectory()}{$serie_directory_config}/";
    $file_name = "{$serie_directory_config}_{$reference->getEntityTypeId()}_{$reference->id()}.rdf";

    // Tests rdf file creation.
    $this->assertFileExists("$directory/$file_name");
    $content = file_get_contents("$directory/$file_name");
    $this->assertTemplateContent($reference, $content);

    // Tests rdf file updation.
    $reference->set('bibcite_abst_e', [
      'value' => 'Test abstract',
    ]);
    $reference->save();
    $this->assertFileExists("$directory/$file_name");
    $content = file_get_contents("$directory/$file_name");
    $this->assertTemplateContent($reference, $content);

    // Tests rdf file deletion.
    $reference->delete();
    $this->assertFileNotExists("$directory/$file_name");
  }

  /**
   * Tests repec as citation distribution plugin.
   *
   * @covers \Drupal\os_publications\Plugin\CitationDistribution\CitationDistributeRepec::save
   * @covers \Drupal\os_publications\Plugin\CitationDistribution\CitationDistributeRepec::delete
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testPluginIntegration() {
    // Positive test.
    $reference = $this->createReference();
    $serie_directory_config = $this->repec->getEntityBundleSettings('serie_directory', $reference->getEntityTypeId(), $reference->bundle());
    $directory = "{$this->repec->getArchiveDirectory()}{$serie_directory_config}/";
    $file_name = "{$serie_directory_config}_{$reference->getEntityTypeId()}_{$reference->id()}.rdf";

    $this->assertFileExists("$directory/$file_name");

    // Negative test.
    $reference = $this->createReference([
      'distribution' => [],
    ]);
    $serie_directory_config = $this->repec->getEntityBundleSettings('serie_directory', $reference->getEntityTypeId(), $reference->bundle());
    $directory = "{$this->repec->getArchiveDirectory()}{$serie_directory_config}/";
    $file_name = "{$serie_directory_config}_{$reference->getEntityTypeId()}_{$reference->id()}.rdf";

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
   * Tests journl template.
   *
   * @covers ::os_publications_repec_template_alter
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testJournlTemplate() {
    // Make sure journal is configured with correct settings.
    /** @var \Drupal\Core\Config\Config $repec_settings_mut */
    $repec_settings_mut = $this->configFactory->getEditable('repec.settings');
    /** @var array $repec_bundle_settings */
    $repec_bundle_settings = $repec_settings_mut->get('repec_bundle');
    $repec_bundle_settings['bibcite_reference']['journal'] = 'a:10:{s:7:"enabled";i:1;s:10:"serie_type";s:6:"journl";s:10:"serie_name";s:6:"journl";s:15:"serie_directory";s:6:"journl";s:17:"restriction_field";s:9:"is_sticky";s:11:"author_name";s:6:"author";s:8:"abstract";s:14:"bibcite_abst_e";s:13:"creation_date";s:7:"created";s:8:"file_url";s:11:"field_files";s:8:"keywords";s:8:"keywords";}';
    $repec_settings_mut->set('repec_bundle', $repec_bundle_settings);
    $repec_settings_mut->save();

    file_put_contents('public://example-1.txt', $this->randomMachineName());
    $file_1 = File::create([
      'uri' => 'public://example-1.txt',
    ]);
    $file_1->save();
    file_put_contents('public://example-2.txt', $this->randomMachineName());
    $file_2 = File::create([
      'uri' => 'public://example-2.txt',
    ]);
    $file_2->save();

    $keyword1 = $this->createKeyword();
    $keyword2 = $this->createKeyword();

    $contributor_1 = $this->createContributor();
    $contributor_2 = $this->createContributor();

    $abstract = $this->randomMachineName();

    $reference = $this->createReference([
      'type' => 'journal',
      'keywords' => [
        [
          'target_id' => $keyword1->id(),
        ],
        [
          'target_id' => $keyword2->id(),
        ],
      ],
      'field_files' => [
        [
          'target_id' => $file_1->id(),
        ],
        [
          'target_id' => $file_2->id(),
        ],
      ],
      'author' => [
        [
          'target_id' => $contributor_1->id(),
        ],
        [
          'target_id' => $contributor_2->id(),
        ],
      ],
      'bibcite_abst_e' => [
        'value' => $abstract,
      ],
    ]);

    $serie_directory_config = $this->repec->getEntityBundleSettings('serie_directory', $reference->getEntityTypeId(), $reference->bundle());
    $directory = "{$this->repec->getArchiveDirectory()}{$serie_directory_config}/";
    $file_name = "{$serie_directory_config}_{$reference->getEntityTypeId()}_{$reference->id()}.rdf";
    $this->assertFileExists("$directory/$file_name");

    $content = file_get_contents("$directory/$file_name");
    $this->assertContains('Template-Type: ReDIF-Paper 1.0', $content);
    $this->assertTemplateContent($reference, $content);
  }

  /**
   * Tests wpaper template.
   *
   * @covers ::os_publications_repec_template_alter
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testWpaperTemplate() {
    // Make sure artwork is configured with correct settings.
    /** @var \Drupal\Core\Config\Config $repec_settings_mut */
    $repec_settings_mut = $this->configFactory->getEditable('repec.settings');
    /** @var array $repec_bundle_settings */
    $repec_bundle_settings = $repec_settings_mut->get('repec_bundle');
    $repec_bundle_settings['bibcite_reference']['artwork'] = 'a:10:{s:7:"enabled";i:1;s:10:"serie_type";s:6:"wpaper";s:10:"serie_name";s:7:"artwork";s:15:"serie_directory";s:6:"wpaper";s:17:"restriction_field";s:9:"is_sticky";s:11:"author_name";s:6:"author";s:8:"abstract";s:14:"bibcite_abst_e";s:13:"creation_date";s:7:"created";s:8:"file_url";s:11:"field_files";s:8:"keywords";s:8:"keywords";}';
    $repec_settings_mut->set('repec_bundle', $repec_bundle_settings);
    $repec_settings_mut->save();

    file_put_contents('public://example-1.txt', $this->randomMachineName());
    $file_1 = File::create([
      'uri' => 'public://example-1.txt',
    ]);
    $file_1->save();
    file_put_contents('public://example-2.txt', $this->randomMachineName());
    $file_2 = File::create([
      'uri' => 'public://example-2.txt',
    ]);
    $file_2->save();

    $keyword1 = $this->createKeyword();
    $keyword2 = $this->createKeyword();

    $contributor_1 = $this->createContributor();
    $contributor_2 = $this->createContributor();

    $abstract = $this->randomMachineName();

    $reference = $this->createReference([
      'keywords' => [
        [
          'target_id' => $keyword1->id(),
        ],
        [
          'target_id' => $keyword2->id(),
        ],
      ],
      'field_files' => [
        [
          'target_id' => $file_1->id(),
        ],
        [
          'target_id' => $file_2->id(),
        ],
      ],
      'author' => [
        [
          'target_id' => $contributor_1->id(),
        ],
        [
          'target_id' => $contributor_2->id(),
        ],
      ],
      'bibcite_abst_e' => [
        'value' => $abstract,
      ],
    ]);

    $serie_directory_config = $this->repec->getEntityBundleSettings('serie_directory', $reference->getEntityTypeId(), $reference->bundle());
    $directory = "{$this->repec->getArchiveDirectory()}{$serie_directory_config}/";
    $file_name = "{$serie_directory_config}_{$reference->getEntityTypeId()}_{$reference->id()}.rdf";
    $this->assertFileExists("$directory/$file_name");

    $content = file_get_contents("$directory/$file_name");
    $this->assertContains('Template-Type: ReDIF-Paper 1.0', $content);
    $this->assertTemplateContent($reference, $content);
  }

  /**
   * Tests ecchap template.
   *
   * @covers ::os_publications_repec_template_alter
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testEcchapTemplate() {
    // Make sure book chapter is configured with correct settings.
    /** @var \Drupal\Core\Config\Config $repec_settings_mut */
    $repec_settings_mut = $this->configFactory->getEditable('repec.settings');
    /** @var array $repec_bundle_settings */
    $repec_bundle_settings = $repec_settings_mut->get('repec_bundle');
    $repec_bundle_settings['bibcite_reference']['book_chapter'] = 'a:10:{s:7:"enabled";i:1;s:10:"serie_type";s:6:"ecchap";s:10:"serie_name";s:12:"Book chapter";s:15:"serie_directory";s:6:"ecchap";s:17:"restriction_field";s:9:"is_sticky";s:11:"author_name";s:6:"author";s:8:"abstract";s:14:"bibcite_abst_e";s:13:"creation_date";s:7:"created";s:8:"file_url";s:11:"field_files";s:8:"keywords";s:8:"keywords";}';
    $repec_settings_mut->set('repec_bundle', $repec_bundle_settings);
    $repec_settings_mut->save();

    file_put_contents('public://example-1.txt', $this->randomMachineName());
    $file_1 = File::create([
      'uri' => 'public://example-1.txt',
    ]);
    $file_1->save();
    file_put_contents('public://example-2.txt', $this->randomMachineName());
    $file_2 = File::create([
      'uri' => 'public://example-2.txt',
    ]);
    $file_2->save();

    $keyword1 = $this->createKeyword();
    $keyword2 = $this->createKeyword();

    $contributor_1 = $this->createContributor();
    $contributor_2 = $this->createContributor();

    $abstract = $this->randomMachineName();

    $reference = $this->createReference([
      'type' => 'book_chapter',
      'keywords' => [
        [
          'target_id' => $keyword1->id(),
        ],
        [
          'target_id' => $keyword2->id(),
        ],
      ],
      'field_files' => [
        [
          'target_id' => $file_1->id(),
        ],
        [
          'target_id' => $file_2->id(),
        ],
      ],
      'author' => [
        [
          'target_id' => $contributor_1->id(),
        ],
        [
          'target_id' => $contributor_2->id(),
        ],
      ],
      'bibcite_abst_e' => [
        'value' => $abstract,
      ],
    ]);

    $serie_directory_config = $this->repec->getEntityBundleSettings('serie_directory', $reference->getEntityTypeId(), $reference->bundle());
    $directory = "{$this->repec->getArchiveDirectory()}{$serie_directory_config}/";
    $file_name = "{$serie_directory_config}_{$reference->getEntityTypeId()}_{$reference->id()}.rdf";
    $this->assertFileExists("$directory/$file_name");

    $content = file_get_contents("$directory/$file_name");
    $this->assertContains('Template-Type: ReDIF-Chapter 1.0', $content);
    $this->assertTemplateContent($reference, $content);
  }

  /**
   * Tests eccode template.
   *
   * @covers ::os_publications_repec_template_alter
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testEccodeTemplate() {
    // Make sure software is configured with correct settings.
    /** @var \Drupal\Core\Config\Config $repec_settings_mut */
    $repec_settings_mut = $this->configFactory->getEditable('repec.settings');
    /** @var array $repec_bundle_settings */
    $repec_bundle_settings = $repec_settings_mut->get('repec_bundle');
    $repec_bundle_settings['bibcite_reference']['software'] = 'a:10:{s:7:"enabled";i:1;s:10:"serie_type";s:6:"eccode";s:10:"serie_name";s:18:"Software component";s:15:"serie_directory";s:6:"eccode";s:17:"restriction_field";s:9:"is_sticky";s:11:"author_name";s:6:"author";s:8:"abstract";s:14:"bibcite_abst_e";s:13:"creation_date";s:7:"created";s:8:"file_url";s:11:"field_files";s:8:"keywords";s:8:"keywords";}';
    $repec_settings_mut->set('repec_bundle', $repec_bundle_settings);
    $repec_settings_mut->save();

    file_put_contents('public://example-1.txt', $this->randomMachineName());
    $file_1 = File::create([
      'uri' => 'public://example-1.txt',
    ]);
    $file_1->save();
    file_put_contents('public://example-2.txt', $this->randomMachineName());
    $file_2 = File::create([
      'uri' => 'public://example-2.txt',
    ]);
    $file_2->save();

    $keyword1 = $this->createKeyword();
    $keyword2 = $this->createKeyword();

    $contributor_1 = $this->createContributor();
    $contributor_2 = $this->createContributor();

    $abstract = $this->randomMachineName();

    $reference = $this->createReference([
      'type' => 'software',
      'keywords' => [
        [
          'target_id' => $keyword1->id(),
        ],
        [
          'target_id' => $keyword2->id(),
        ],
      ],
      'field_files' => [
        [
          'target_id' => $file_1->id(),
        ],
        [
          'target_id' => $file_2->id(),
        ],
      ],
      'author' => [
        [
          'target_id' => $contributor_1->id(),
        ],
        [
          'target_id' => $contributor_2->id(),
        ],
      ],
      'bibcite_abst_e' => [
        'value' => $abstract,
      ],
    ]);

    $serie_directory_config = $this->repec->getEntityBundleSettings('serie_directory', $reference->getEntityTypeId(), $reference->bundle());
    $directory = "{$this->repec->getArchiveDirectory()}{$serie_directory_config}/";
    $file_name = "{$serie_directory_config}_{$reference->getEntityTypeId()}_{$reference->id()}.rdf";
    $this->assertFileExists("$directory/$file_name");

    $content = file_get_contents("$directory/$file_name");
    $this->assertContains('Template-Type: ReDIF-Software 1.0', $content);
    $this->assertTemplateContent($reference, $content);
  }

  /**
   * Tests ecbook template.
   *
   * @covers ::os_publications_repec_template_alter
   * @covers ::os_publications_repec_template_ecbook_alter
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testEcbookTemplate() {
    // Make sure book is configured with correct settings.
    /** @var \Drupal\Core\Config\Config $repec_settings_mut */
    $repec_settings_mut = $this->configFactory->getEditable('repec.settings');
    /** @var array $repec_bundle_settings */
    $repec_bundle_settings = $repec_settings_mut->get('repec_bundle');
    $repec_bundle_settings['bibcite_reference']['book'] = 'a:11:{s:7:"enabled";i:1;s:10:"serie_type";s:6:"ecbook";s:10:"serie_name";s:4:"Book";s:15:"serie_directory";s:6:"ecbook";s:17:"restriction_field";s:9:"is_sticky";s:11:"author_name";s:6:"author";s:8:"abstract";s:14:"bibcite_abst_e";s:13:"creation_date";s:7:"created";s:8:"file_url";s:11:"field_files";s:8:"keywords";s:8:"keywords";s:13:"provider_name";s:17:"bibcite_publisher";}';
    $repec_settings_mut->set('repec_bundle', $repec_bundle_settings);
    $repec_settings_mut->save();

    file_put_contents('public://example-1.txt', $this->randomMachineName());
    $file_1 = File::create([
      'uri' => 'public://example-1.txt',
    ]);
    $file_1->save();
    file_put_contents('public://example-2.txt', $this->randomMachineName());
    $file_2 = File::create([
      'uri' => 'public://example-2.txt',
    ]);
    $file_2->save();

    $keyword1 = $this->createKeyword();
    $keyword2 = $this->createKeyword();

    $contributor_1 = $this->createContributor();
    $contributor_2 = $this->createContributor();

    $abstract = $this->randomString();

    $publisher = $this->randomMachineName();

    $reference = $this->createReference([
      'type' => 'book',
      'keywords' => [
        [
          'target_id' => $keyword1->id(),
        ],
        [
          'target_id' => $keyword2->id(),
        ],
      ],
      'field_files' => [
        [
          'target_id' => $file_1->id(),
        ],
        [
          'target_id' => $file_2->id(),
        ],
      ],
      'author' => [
        [
          'target_id' => $contributor_1->id(),
        ],
        [
          'target_id' => $contributor_2->id(),
        ],
      ],
      'bibcite_abst_e' => [
        'value' => $abstract,
      ],
      'bibcite_publisher' => [
        'value' => $publisher,
      ],
    ]);

    $serie_directory_config = $this->repec->getEntityBundleSettings('serie_directory', $reference->getEntityTypeId(), $reference->bundle());
    $directory = "{$this->repec->getArchiveDirectory()}{$serie_directory_config}/";
    $file_name = "{$serie_directory_config}_{$reference->getEntityTypeId()}_{$reference->id()}.rdf";
    $this->assertFileExists("$directory/$file_name");

    $content = file_get_contents("$directory/$file_name");
    $this->assertContains('Template-Type: ReDIF-Book 1.0', $content);
    $this->assertContains("Provider-Name: {$publisher}", $content);
    $this->assertTemplateContent($reference, $content);
  }

  /**
   * Asserts template content of a reference.
   *
   * @param \Drupal\bibcite_entity\Entity\ReferenceInterface $reference
   *   The reference entity. This is used as the expected data.
   * @param string $content
   *   The actual content.
   */
  protected function assertTemplateContent(ReferenceInterface $reference, $content) {
    $this->assertContains("Title: {$reference->label()}", $content);
    $this->assertContains("Number: {$reference->uuid()}", $content);
    $this->assertContains("Handle: RePEc:{$this->defaultRepecSettings['archive_code']}:{$this->repec->getEntityBundleSettings('serie_type', $reference->getEntityTypeId(), $reference->bundle())}:{$reference->id()}", $content);

    // Assert keywords.
    $keyword_names = [];
    foreach ($reference->get('keywords') as $item) {
      $keyword = Keyword::load($item->getValue()['target_id']);
      $keyword_names[] = $keyword->getName();
    }

    if ($keyword_names) {
      $keyword_names_in_template = implode(', ', $keyword_names);
      $this->assertContains("Keywords: {$keyword_names_in_template}", $content);
    }

    // Assert files.
    $files_data = [];
    foreach ($reference->get('field_files') as $item) {
      $file = File::load($item->getValue()['target_id']);
      $files_data[] = [
        'url' => file_create_url($file->getFileUri()),
        'type' => ucfirst($file->getMimeType()),
      ];
    }

    foreach ($files_data as $datum) {
      $this->assertContains("File-URL: {$datum['url']}", $content);
      $this->assertContains("File-Format: {$datum['type']}", $content);
    }

    // Assert authors.
    foreach ($reference->get('author') as $item) {
      $contributor = Contributor::load($item->getValue()['target_id']);
      $this->assertContains("Author-Name: {$contributor->getName()}", $content);
    }

    /** @var array $abstract */
    if ($abstract = $reference->get('bibcite_abst_e')->getValue()) {
      $this->assertContains("Abstract: {$abstract[0]['value']}", $content);
    }
  }

  /**
   * Changes the citation distribution mode in the setting.
   *
   * @param string $mode
   *   The mode.
   */
  protected function changeCitationDistributionMode($mode) {
    /** @var \Drupal\Core\Config\Config $citation_distribution_settings_mut */
    $citation_distribution_settings_mut = $this->configFactory->getEditable('citation_distribute.settings');
    $citation_distribution_settings_mut->set('citation_distribute_module_mode', $mode);
    $citation_distribution_settings_mut->save();
  }

  /**
   * {@inheritdoc}
   */
  public function tearDown() {
    /** @var \Drupal\Core\Config\Config $repec_settings_mut */
    $repec_settings_mut = $this->configFactory->getEditable('repec.settings');
    $repec_settings_mut->setData($this->defaultRepecSettings);
    $repec_settings_mut->save(TRUE);

    /** @var \Drupal\Core\Config\Config $citation_distribution_settings_mut */
    $citation_distribution_settings_mut = $this->configFactory->getEditable('citation_distribute.settings');
    $citation_distribution_settings_mut->setData($this->defaultCitationDistributionSettings);
    $citation_distribution_settings_mut->save(TRUE);

    parent::tearDown();
  }

}
