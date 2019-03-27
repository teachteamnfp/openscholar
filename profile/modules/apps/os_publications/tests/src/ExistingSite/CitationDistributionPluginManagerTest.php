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

    $template_path = $this->getRepecTemplatePath($published_reference);

    $this->assertFileExists($template_path);

    // Assert positive update.
    $published_reference->set('bibcite_abst_e', [
      'value' => 'Test abstract',
    ]);
    $published_reference->save();

    $this->assertFileExists($template_path);

    // Assert negative insert.
    $unpublished_reference = $this->createReference([
      'status' => [
        'value' => 0,
      ],
    ]);

    $template_path = $this->getRepecTemplatePath($unpublished_reference);

    $this->assertFileNotExists($template_path);

    // Assert negative update.
    $unpublished_reference->set('bibcite_abst_e', [
      'value' => 'Test abstract',
    ]);
    $unpublished_reference->save();

    $this->assertFileNotExists($template_path);
  }

}
