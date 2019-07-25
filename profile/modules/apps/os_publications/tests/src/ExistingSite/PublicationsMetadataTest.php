<?php

namespace Drupal\Tests\os_publications\ExistingSite;

/**
 * Class PublicationsMetadataTest.
 *
 * @group functional
 * @group publications
 */
class PublicationsMetadataTest extends TestBase {

  /**
   * Group administrator.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $groupAdmin;

  /**
   * Reference content.
   *
   * @var \Drupal\bibcite_entity\Entity\ReferenceInterface
   */
  protected $reference;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->group = $this->createGroup();
    $this->groupAdmin = $this->createUser();
    $this->addGroupAdmin($this->groupAdmin, $this->group);
    $this->drupalLogin($this->groupAdmin);
  }

  /**
   * Test Metadata on entity page.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testGoogleScholarMetadata() {
    $this->drupalLogin($this->groupAdmin);
    $reference = $this->createReference([
      'distribution' => [
        'citation_distribute_googlescholar',
      ],
    ]);
    $this->group->addContent($reference, 'group_entity:bibcite_reference');
    $this->visitViaVsite('bibcite/reference/' . $reference->id(), $this->group);
    $this->assertSession()->responseContains('citation_title');
    $this->assertSession()->responseContains('citation_year');
  }

  /**
   * Test noindex setting has effect on publication.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testPublicationNoIndex(): void {
    // Test positive case.
    $reference1 = $this->createReference([
      'noindex' => TRUE,
    ]);
    $this->group->addContent($reference1, 'group_entity:bibcite_reference');
    $this->visitViaVsite('bibcite/reference/' . $reference1->id(), $this->group);
    $this->assertSession()->responseContains('NOINDEX');
    $this->assertSession()->responseContains('NOFOLLOW');

    // Test negative case.
    $reference2 = $this->createReference();
    $this->group->addContent($reference2, 'group_entity:bibcite_reference');
    $this->visitViaVsite('bibcite/reference/' . $reference2->id(), $this->group);
    $this->assertSession()->responseNotContains('NOINDEX');
    $this->assertSession()->responseNotContains('NOFOLLOW');
  }

}
