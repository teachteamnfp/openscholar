<?php

namespace Drupal\Tests\os_publications\ExistingSite;

use Drupal\bibcite_entity\Entity\Contributor;
use Drupal\bibcite_entity\Entity\Keyword;
use Drupal\bibcite_entity\Entity\ReferenceInterface;
use Drupal\file\Entity\File;
use Drupal\Tests\openscholar\ExistingSite\OsExistingSiteTestBase;
use Drupal\Tests\os_publications\Traits\OsPublicationsTestTrait;

/**
 * TestBase for bibcite customizations.
 */
abstract class TestBase extends OsExistingSiteTestBase {

  use OsPublicationsTestTrait;

  /**
   * Default repec settings.
   *
   * @var array
   */
  protected $defaultRepecSettings;

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
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->configFactory = $this->container->get('config.factory');
    $this->repec = $this->container->get('repec');
    $this->defaultRepecSettings = $this->configFactory->get('repec.settings')->getRawData();
    $this->repec->initializeTemplates();
  }

  /**
   * Asserts template content of a reference.
   *
   * @param \Drupal\bibcite_entity\Entity\ReferenceInterface $reference
   *   The reference entity. This is used as the expected data.
   * @param string $content
   *   The actual content.
   */
  protected function assertTemplateContent(ReferenceInterface $reference, $content): void {
    $this->assertStringStartsWith('Template-Type', $content);
    $this->assertContains("Title: {$reference->label()}", $content);
    $this->assertContains("Number: {$reference->uuid()}", $content);
    $this->assertContains("Handle: RePEc:{$this->defaultRepecSettings['archive_code']}:{$this->repec->getEntityBundleSettings('serie_type', $reference->getEntityTypeId(), $reference->bundle())}:{$reference->id()}", $content);

    $created_date = date('Y-m-d', $reference->getCreatedTime());
    $this->assertContains("Creation-Date: {$created_date}", $content);

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
    $abstract = $reference->get('bibcite_abst_e')->getValue();
    if ($abstract) {
      $this->assertContains("Abstract: {$abstract[0]['value']}", $content);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function tearDown() {
    /** @var \Drupal\Core\File\FileSystemInterface $file_system */
    $file_system = $this->container->get('file_system');
    $template_path = "{$this->repec->getArchiveDirectory()}/{$this->defaultRepecSettings['archive_code']}seri.rdf";
    $real_path = $file_system->realpath($template_path);

    if (file_exists($real_path)) {
      unlink($real_path);
    }

    parent::tearDown();
  }

}
