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

}
