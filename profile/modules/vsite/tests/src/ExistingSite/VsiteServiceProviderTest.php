<?php

namespace Drupal\Tests\vsite\ExistingSite;

/**
 * Tests VsiteServiceProvider.
 *
 * @group vsite
 * @group kernel
 * @coversDefaultClass \Drupal\vsite\VsiteServiceProvider
 */
class VsiteServiceProviderTest extends VsiteExistingSiteTestBase {

  /**
   * Tests alteration in purl path processor.
   *
   * @covers \Drupal\vsite\VsiteServiceProvider::alter
   */
  public function testAlterPurlPathProcessor() {
    $definition = $this->container->getDefinition('purl.outbound_path_processor');
    $tags = $definition->getTags();
    $this->assertEquals(290, $tags['path_processor_outbound'][0]['priority']);
  }

}
