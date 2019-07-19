<?php

namespace Drupal\Tests\os_publications\ExistingSite;

/**
 * Class CitationHelperTest.
 *
 * @group kernel
 * @group publications
 *
 * @package Drupal\Tests\os_publications\ExistingSite
 */
class CitationHelperTest extends TestBase {

  /**
   * Citation helper service.
   *
   * @var \Drupal\os_publications\CitationHelperInterface
   */
  protected $citationHelper;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->citationHelper = $this->container->get('os_publications.citation_helper');
  }

  /**
   * Tests testCitationDownloadButtonService.
   */
  public function testCitationDownloadButtonService(): void {
    // Test service returns button.
    $buttonArray = $this->citationHelper->getCitationDownloadButton();
    $this->assertNotEmpty($buttonArray);

    // Test service returns multiple export route when no argument passed.
    $multiple = $this->citationHelper->getCitationDownloadButton();
    foreach ($multiple['#items'] as $item) {
      if (isset($item['#url'])) {
        $actual = $item['#url'];
        break;
      }
    }
    $expected = 'os_publictions.citation_export_multiple';
    $this->assertSame($actual->getRouteName(), $expected);

    // Test service returns single export route when argument is passed.
    $single = $this->citationHelper->getCitationDownloadButton($this->ref1->id());
    foreach ($single['#items'] as $item) {
      if (isset($item['#url'])) {
        $actual = $item['#url'];
        break;
      }
    }
    $expected = 'os_publictions.citation_export';
    $this->assertSame($actual->getRouteName(), $expected);
  }

}
