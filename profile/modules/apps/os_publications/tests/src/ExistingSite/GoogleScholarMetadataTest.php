<?php

namespace Drupal\Tests\os_publications\ExistingSite;

/**
 * Class PublicationsFormTest.
 *
 * @group functional
 * @group publications
 */
class GoogleScholarMetadataTest extends TestBase {

  /**
   * Admin user.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $admin;

  /**
   * Normal user.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $user;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->admin = $this->createUser([], '', TRUE);
  }

  /**
   * Test Metadata on entity page.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testGoogleScholarMetadata() {
    $this->drupalLogin($this->admin);
    $reference = $this->createReference([
      'distribution' => [
        'citation_distribute_googlescholar',
      ],
    ]);
    $this->drupalGet('bibcite/reference/' . $reference->id());
    $this->assertSession()->responseContains('citation_title');
    $this->assertSession()->responseContains('citation_year');
  }

}
