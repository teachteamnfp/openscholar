<?php

namespace Drupal\Tests\os_publications\ExistingSite;

/**
 * RepecTest.
 *
 * @group kernel
 * @group publications
 */
class RepecTest extends TestBase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    // Make sure before every test case, the series template is not present.
    /** @var \Drupal\Core\File\FileSystemInterface $file_system */
    $file_system = $this->container->get('file_system');
    $template_path = "{$this->repec->getArchiveDirectory()}/{$this->defaultRepecSettings['archive_code']}seri.rdf";
    $real_path = $file_system->realpath($template_path);

    if (file_exists($real_path)) {
      unlink($real_path);
    }
  }

  /**
   * @covers \Drupal\repec\Repec::shouldCreateSeriesTemplate
   */
  public function testShouldCreateSeriesTemplate(): void {
    $settings = $this->repec->getEntityBundleSettings('all', 'bibcite_reference', 'artwork');

    // Positive tests.
    $this->assertTrue($this->repec->shouldCreateSeriesTemplate($settings));

    $template_path = "{$this->repec->getArchiveDirectory()}/{$this->defaultRepecSettings['archive_code']}seri.rdf";
    file_put_contents($template_path, $this->randomMachineName());

    $this->assertTrue($this->repec->shouldCreateSeriesTemplate($settings));

    // Negative tests.
    file_put_contents($template_path, 'Name: artwork', FILE_APPEND);

    $this->assertFalse($this->repec->shouldCreateSeriesTemplate($settings));

    // Clean up.
    /** @var \Drupal\Core\File\FileSystemInterface $file_system */
    $file_system = $this->container->get('file_system');
    unlink($file_system->realpath($template_path));
  }

  /**
   * @covers \Drupal\repec\Repec::appendTemplate
   */
  public function testAppendTemplate(): void {
    $template_path = "{$this->repec->getArchiveDirectory()}/{$this->defaultRepecSettings['archive_code']}seri.rdf";
    file_put_contents($template_path, 'I came here first');

    $this->repec->appendTemplate([
      [
        'attribute' => 'Template-Type',
        'value' => 'ReDIF-Series 1.0',
      ],
    ], 'seri');

    $content = file_get_contents($template_path);

    $this->assertContains('I came here first', $content);
    $this->assertContains('Template-Type: ReDIF-Series 1.0', $content);
  }

}
