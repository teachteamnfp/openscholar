<?php

namespace Drupal\Tests\os_publications\ExistingSite;

use Drupal\bibcite_entity\Entity\Contributor;
use Drupal\bibcite_entity\Entity\ContributorInterface;
use Drupal\bibcite_entity\Entity\Keyword;
use Drupal\bibcite_entity\Entity\KeywordInterface;
use Drupal\bibcite_entity\Entity\Reference;
use Drupal\bibcite_entity\Entity\ReferenceInterface;
use Drupal\file\Entity\File;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * TestBase for bibcite customizations.
 */
abstract class TestBase extends ExistingSiteBase {

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
   * Creates a reference.
   *
   * @param array $values
   *   (Optional) Default values for the reference.
   *
   * @return \Drupal\bibcite_entity\Entity\ReferenceInterface
   *   The new reference entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function createReference(array $values = []) : ReferenceInterface {
    $reference = Reference::create($values + [
      'title' => $this->randomMachineName(),
      'type' => 'artwork',
      'bibcite_year' => [
        'value' => 1980,
      ],
      'distribution' => [
        [
          'value' => 'citation_distribute_repec',
        ],
      ],
      'status' => [
        'value' => 1,
      ],
    ]);

    $reference->save();

    $this->markEntityForCleanup($reference);

    return $reference;
  }

  /**
   * Creates a contributor.
   *
   * @param array $values
   *   (Optional) Default values for the contributor.
   *
   * @return \Drupal\bibcite_entity\Entity\ContributorInterface
   *   The new contributor entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function createContributor(array $values = []) : ContributorInterface {
    $contributor = Contributor::create($values + [
      'first_name' => $this->randomString(),
      'middle_name' => $this->randomString(),
      'last_name' => $this->randomString(),
    ]);

    $contributor->save();

    $this->markEntityForCleanup($contributor);

    return $contributor;
  }

  /**
   * Creates a keyword.
   *
   * @param array $values
   *   (Optional) Default values for the keyword.
   *
   * @return \Drupal\bibcite_entity\Entity\KeywordInterface
   *   The new keyword entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function createKeyword(array $values = []) : KeywordInterface {
    $keyword = Keyword::create($values + [
      'name' => $this->randomString(),
    ]);

    $keyword->save();

    $this->markEntityForCleanup($keyword);

    return $keyword;
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
   * Returns the rdf file template path.
   *
   * The path is already URI prefixed, i.e. prefixed with `public://`.
   *
   * @param \Drupal\bibcite_entity\Entity\ReferenceInterface $reference
   *   The reference whose template path to be obtained.
   *
   * @return string
   *   The template path.
   */
  protected function getRepecTemplatePath(ReferenceInterface $reference): string {
    $serie_directory_config = $this->repec->getEntityBundleSettings('serie_directory', $reference->getEntityTypeId(), $reference->bundle());
    $directory = "{$this->repec->getArchiveDirectory()}{$serie_directory_config}/";
    $file_name = "{$serie_directory_config}_{$reference->getEntityTypeId()}_{$reference->id()}.rdf";

    return "$directory/$file_name";
  }

}
