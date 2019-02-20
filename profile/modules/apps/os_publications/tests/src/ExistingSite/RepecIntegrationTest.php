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
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->repec = $this->container->get('repec');
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
   * @covers \Drupal\repec\Repec::createPaperTemplate
   */
  public function testEntityShareable() {
    // Create reference with "is shared" setting off.
    // Assert presence of rdf file in public://
    // Create reference with "is shared" setting on.
    // Assert not presence of rdf file in public://.
  }

}
