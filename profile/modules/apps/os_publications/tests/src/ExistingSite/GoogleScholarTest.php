<?php

namespace Drupal\Tests\os_publications\ExistingSite;

/**
 * Class PublicationsFormTest.
 *
 * @group kernel
 *
 * @package Drupal\Tests\os_publications\ExistingSite
 */
class GoogleScholarTest extends TestBase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->user = $this->createUser([], '', TRUE);
    $this->simpleUser = $this->createUser();
  }

  /**
   * Test Setting form route.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testPublicationSettingsPath() {
    $this->drupalLogin($this->user);
    $reference = $this->createReference();

    $this->drupalGet('bibcite/reference/' . $reference->id());
    drupal_flush_all_caches();
    $this->drupalGet('bibcite/reference/' . $reference->id());
    $this->assertSession()->statusCodeEquals(200);
  }

  /**
   * Test Metadata on entity page.
   */
  public function testGoogleScholarMetatdata() {

    $this->drupalLogin($this->user);
    $reference = $this->createReference();
    $this->drupalGet('bibcite/reference/' . $reference->id());
    $html = $this->getCurrentPage()->getHtml();
    $this->assertContains('citation_title', $html);
    $this->assertContains('citation_year', $html);
  }

}
