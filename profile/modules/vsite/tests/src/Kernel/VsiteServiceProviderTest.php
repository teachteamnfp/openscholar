<?php

namespace Drupal\Tests\vsite\Kernel;

use Drupal\Tests\group\Kernel\GroupKernelTestBase;

/**
 * Tests VsiteServiceProvider.
 *
 * @group vsite
 * @group kernel
 * @coversDefaultClass \Drupal\vsite\VsiteServiceProvider
 */
class VsiteServiceProviderTest extends GroupKernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'purl',
    'vsite',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installConfig([
      'purl',
      'vsite',
    ]);
  }

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
