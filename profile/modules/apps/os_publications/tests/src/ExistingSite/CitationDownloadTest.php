<?php

namespace Drupal\Tests\os_publications\ExistingSite;

/**
 * Class CitationDownloadTest.
 *
 * @group kernel
 * @group publications
 *
 * @package Drupal\Tests\os_publications\ExistingSite
 */
class CitationDownloadTest extends TestBase {

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
    $this->ref1 = $this->createReference([
      'html_title' => 'This is export test',
    ]);
    $this->ref2 = $this->createReference([
      'html_title' => 'This is another export test',
      'type' => 'miscellaneous',
    ]);
    $this->group->addContent($this->ref1, 'group_entity:bibcite_reference');
    $this->group->addContent($this->ref2, 'group_entity:bibcite_reference');
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

  /**
   * Tests citation download on node page.
   */
  public function testCitationDownloadOnPublicationNodePage(): void {
    // Test Bibtext format output on node page.
    $expected_text = '@artwork{' . $this->ref1->id() . ',title={Thisisexporttest},year={1980},}';
    $text = $this->drupalGet($this->group->get('path')->getValue()[0]['alias'] . '/citation/export/bibtex/' . $this->ref1->id());
    $actual_text = preg_replace('/\s+/', '', $text);
    $this->assertSame($expected_text, $actual_text);

    // Test EndNote7Xml format on node page.
    $expected_text = '<?xmlversion="1.0"encoding="UTF-8"?><xml><records><RECORD><source-appname="Bibcite"version="8.x">Drupal-Bibcite</source-app><REFERENCE_TYPE>13</REFERENCE_TYPE><CONTRIBUTORS><AUTHORS/></CONTRIBUTORS><TITLES><TITLE><styleface="normal"font="default"size="100%">Thisisexporttest</style></TITLE></TITLES><KEYWORDS/><DATES/><YEAR><styleface="normal"font="default"size="100%">1980</style></YEAR><title><styleface="normal"font="default"size="100%">Thisisexporttest</style></title></RECORD></records></xml>';
    $text = $this->drupalGet($this->group->get('path')->getValue()[0]['alias'] . '/citation/export/endnote7/' . $this->ref1->id());
    $actual_text = preg_replace('/\s+/', '', $text);
    $this->assertSame($expected_text, $actual_text);

    // Test EndNote Tagged Xml on node page.
    $expected_text = '%0Artwork%D1980%TThisisexporttest';
    $text = $this->drupalGet($this->group->get('path')->getValue()[0]['alias'] . '/citation/export/tagged/' . $this->ref1->id());
    $actual_text = preg_replace('/\s+/', '', $text);
    $this->assertSame($expected_text, $actual_text);

  }

  /**
   * Tests citation download on publication listing page.
   */
  public function testCitationDownloadOnListingViewPage(): void {
    // Test Bibtext format output on node page.
    $expected_text = '@artwork{' . $this->ref1->id() . ',title={Thisisexporttest},year={1980},}@misc{' . $this->ref2->id() . ',title={Thisisanotherexporttest},year={1980},}';
    $text = $this->drupalGet($this->group->get('path')->getValue()[0]['alias'] . '/citation/export/bibtex');
    $actual_text = preg_replace('/\s+/', '', $text);
    $this->assertSame($expected_text, $actual_text);

    // Test EndNote7Xml format on node page.
    $expected_text = '<?xmlversion="1.0"encoding="UTF-8"?><xml><records><RECORD><source-appname="Bibcite"version="8.x">Drupal-Bibcite</source-app><REFERENCE_TYPE>13</REFERENCE_TYPE><CONTRIBUTORS><AUTHORS/></CONTRIBUTORS><TITLES><TITLE><styleface="normal"font="default"size="100%">Thisisexporttest</style></TITLE></TITLES><KEYWORDS/><DATES/><YEAR><styleface="normal"font="default"size="100%">1980</style></YEAR><title><styleface="normal"font="default"size="100%">Thisisexporttest</style></title></RECORD><RECORD><source-appname="Bibcite"version="8.x">Drupal-Bibcite</source-app><REFERENCE_TYPE>31</REFERENCE_TYPE><CONTRIBUTORS><AUTHORS/></CONTRIBUTORS><TITLES><TITLE><styleface="normal"font="default"size="100%">Thisisanotherexporttest</style></TITLE></TITLES><KEYWORDS/><DATES/><YEAR><styleface="normal"font="default"size="100%">1980</style></YEAR><title><styleface="normal"font="default"size="100%">Thisisanotherexporttest</style></title></RECORD></records></xml>';
    $text = $this->drupalGet($this->group->get('path')->getValue()[0]['alias'] . '/citation/export/endnote7');
    $actual_text = preg_replace('/\s+/', '', $text);
    $this->assertSame($expected_text, $actual_text);

    // Test EndNote Tagged Xml on node page.
    $expected_text = '%0Artwork%D1980%TThisisexporttest%0Generic%D1980%TThisisanotherexporttest';
    $text = $this->drupalGet($this->group->get('path')->getValue()[0]['alias'] . '/citation/export/tagged');
    $actual_text = preg_replace('/\s+/', '', $text);
    $this->assertSame($expected_text, $actual_text);

  }

}
