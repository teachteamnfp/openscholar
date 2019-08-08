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
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->citationHelper = $this->container->get('os_publications.citation_helper');
    $this->ref1 = $this->createReference([
      'html_title' => 'This is export test',
    ]);
    $this->group->addContent($this->ref1, 'group_entity:bibcite_reference');
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
   * Tests author alteration service.
   */
  public function testAuthorAlter() : void {
    $data['author'] = [
      [
        'family' => 'Doe',
        'given' => 'John',
        'category' => 'primary',
        'role' => 'author',
      ],
      [
        'family' => 'Smith',
        'given' => 'Richard',
        'category' => 'primary',
        'role' => 'author',
      ],
    ];
    $data['editor'] = [
      [
        'family' => 'Editor',
        'given' => 'Edwin',
        'category' => 'primary',
        'role' => 'editor',
      ],
    ];

    $this->citationHelper->alterAuthors($data);
    // Test name is changed for an author role.
    $this->assertEquals('John Doe', $data['author'][0]['given']);
    // Test family key is empty for an author role.
    $this->assertEmpty($data['author'][1]['family']);
    // Test editor information is added for editor role.
    $this->assertContains('Edited By', $data['editor'][0]['given']);
  }

}
