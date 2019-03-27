<?php

namespace Drupal\Tests\os_publications\ExistingSite;

/**
 * CitationDistributionPluginManagerTest.
 *
 * @group kernel
 */
class CitationDistributionPluginManagerTest extends TestBase {

  /**
   * Tests citation distribution.
   *
   * Relying on Repec plugin for carrying out the tests.
   *
   * @covers \Drupal\os_publications\Plugin\CitationDistribution\CitationDistributePluginManager::distribute
   * @covers ::os_publications_bibcite_reference_insert
   * @covers ::os_publications_bibcite_reference_update
   */
  public function testDistribute() {
    // Assert positive insert.
    $published_reference = $this->createReference();

    $serie_directory_config = $this->repec->getEntityBundleSettings('serie_directory', $published_reference->getEntityTypeId(), $published_reference->bundle());
    $directory = "{$this->repec->getArchiveDirectory()}{$serie_directory_config}/";
    $file_name = "{$serie_directory_config}_{$published_reference->getEntityTypeId()}_{$published_reference->id()}.rdf";

    $this->assertFileExists("$directory/$file_name");

    // Assert positive update.
    $published_reference->set('bibcite_abst_e', [
      'value' => 'Test abstract',
    ]);
    $published_reference->save();

    $this->assertFileExists("$directory/$file_name");

    // Assert negative insert.
    $unpublished_reference = $this->createReference([
      'status' => [
        'value' => 0,
      ],
    ]);

    $serie_directory_config = $this->repec->getEntityBundleSettings('serie_directory', $unpublished_reference->getEntityTypeId(), $unpublished_reference->bundle());
    $directory = "{$this->repec->getArchiveDirectory()}{$serie_directory_config}/";
    $file_name = "{$serie_directory_config}_{$unpublished_reference->getEntityTypeId()}_{$unpublished_reference->id()}.rdf";

    $this->assertFileNotExists("$directory/$file_name");

    // Assert negative update.
    $unpublished_reference->set('bibcite_abst_e', [
      'value' => 'Test abstract',
    ]);
    $unpublished_reference->save();

    $this->assertFileNotExists("$directory/$file_name");
  }

}
