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

    $this->drupalLogin($this->groupAdmin);
  }

  /**
   * Tests export button on node page.
   */
  public function testCitationExportLinksOnNodePage(): void {

    // Test Citation download is available when formats are enabled.
    $this->visitViaVsite('bibcite/reference/' . $this->ref1->id(), $this->group);
    $this->assertSession()->elementExists('css', '.citation-download');

    // Make changes.
    $this->visitViaVsite('cp/settings/publications', $this->group);
    $this->drupalPostForm(NULL, [
      'os_publications_export_format[bibtex]' => FALSE,
      'os_publications_export_format[endnote8]' => FALSE,
      'os_publications_export_format[endnote7]' => FALSE,
      'os_publications_export_format[tagged]' => FALSE,
      'os_publications_export_format[ris]' => FALSE,
    ], 'Save configuration');

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
    $this->visitViaVsite('cp/settings/publications', $this->group);
    $this->drupalPostForm(NULL, [
      'os_publications_export_format[bibtex]' => FALSE,
      'os_publications_export_format[endnote8]' => FALSE,
      'os_publications_export_format[endnote7]' => FALSE,
      'os_publications_export_format[tagged]' => FALSE,
      'os_publications_export_format[ris]' => FALSE,
    ], 'Save configuration');

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
