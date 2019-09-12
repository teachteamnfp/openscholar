<?php

namespace Drupal\Tests\os_publications\ExistingSite;

/**
 * Class CitationDownloadTest.
 *
 * @group functional
 * @group publications
 *
 * @package Drupal\Tests\os_publications\ExistingSite
 */
class CitationExportLinksTest extends TestBase {

  /**
   * Citation helper service.
   *
   * @var \Drupal\os_publications\CitationHelperInterface
   */
  protected $citationHelper;

  /**
   * Group Admin user.
   *
   * @var \Drupal\user\Entity\User|false
   */
  protected $groupAdmin;

  /**
   * Reference content.
   *
   * @var \Drupal\bibcite_entity\Entity\ReferenceInterface
   */
  protected $ref1;

  /**
   * Reference content.
   *
   * @var \Drupal\bibcite_entity\Entity\ReferenceInterface
   */
  protected $ref2;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->citationHelper = $this->container->get('os_publications.citation_helper');
    $this->groupAdmin = $this->createUser();
    $this->addGroupAdmin($this->groupAdmin, $this->group);
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

  /**
   * Tests export button on node page.
   */
  public function testCitationExportLinksOnNodePage(): void {

    // Test Citation download is available when formats are enabled.
    $this->visitViaVsite('bibcite/reference/' . $this->ref1->id(), $this->group);
    $this->assertSession()->elementExists('css', '.citation-download');

    // Make changes.
    $this->drupalLogin($this->groupAdmin);
    $this->visitViaVsite('cp/settings/apps-settings/publications', $this->group);
    $this->drupalPostForm(NULL, [
      'os_publications_export_format[bibtex]' => FALSE,
      'os_publications_export_format[endnote8]' => FALSE,
      'os_publications_export_format[endnote7]' => FALSE,
      'os_publications_export_format[tagged]' => FALSE,
      'os_publications_export_format[ris]' => FALSE,
    ], 'Save configuration');

    $this->drupalLogout();

    // Test Citation download is not available when formats are disabled.
    $this->visitViaVsite('bibcite/reference/' . $this->ref1->id(), $this->group);
    $this->assertSession()->elementNotExists('css', '.citation-download');
  }

  /**
   * Tests export button on publication listing page.
   */
  public function testCitationExportLinksOnListingViewPage(): void {

    // Test Citation download is available when formats are enabled.
    $this->visitViaVsite('publications', $this->group);
    $this->assertSession()->elementExists('css', '.citation-download');

    // Make changes.
    $this->drupalLogin($this->groupAdmin);
    $this->visitViaVsite('cp/settings/apps-settings/publications', $this->group);
    $this->drupalPostForm(NULL, [
      'os_publications_export_format[bibtex]' => FALSE,
      'os_publications_export_format[endnote8]' => FALSE,
      'os_publications_export_format[endnote7]' => FALSE,
      'os_publications_export_format[tagged]' => FALSE,
      'os_publications_export_format[ris]' => FALSE,
    ], 'Save configuration');

    $this->drupalLogout();

    // Test Citation download is not available when formats are disabled.
    $this->visitViaVsite('publications', $this->group);
    $this->assertSession()->elementNotExists('css', '.citation-download');
  }

  /**
   * Tests PubMed Link on node page.
   */
  public function testPubmedLinkOnNodePage(): void {

    $ref3 = $this->createReference([
      'html_title' => 'This is pubmed test.',
      'bibcite_pmid' => $this->randomString(),
    ]);
    $this->group->addContent($ref3, 'group_entity:bibcite_reference');

    // Test pubmed link doesnt appear when no PMID is entered.
    $this->visitViaVsite('bibcite/reference/' . $this->ref1->id(), $this->group);
    $this->assertSession()->elementNotExists('css', '.citation-links-pubmed');

    // Test pubmed link appears when PMID is entered.
    $this->visitViaVsite('bibcite/reference/' . $ref3->id(), $this->group);
    $this->assertSession()->elementExists('css', '.citation-links-pubmed');
  }

}
