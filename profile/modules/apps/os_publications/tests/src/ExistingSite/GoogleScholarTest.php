<?php

namespace Drupal\Tests\os_publications\ExistingSite;

use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Class PublicationsFormTest.
 *
 * @group kernel
 *
 * @package Drupal\Tests\os_publications\ExistingSite
 */
class GoogleScholarTest extends ExistingSiteBase {

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
   */
  public function testPublicationSettingsPath() {
    $this->drupalLogin($this->user);
    $this->drupalGet('bibcite/reference/1');
    drupal_flush_all_caches();
    $this->drupalGet('bibcite/reference/1');
    $this->assertSession()->statusCodeEquals(200);
  }

  /**
   * Test Metadata on entity page.
   */
  public function testGoogleScholarMetatdata() {

    $this->drupalLogin($this->user);
    $this->drupalGet('bibcite/reference/1');
    $html = $this->getCurrentPage()->getHtml();
    $this->assertContains('citation_title', $html);
    $this->assertContains('citation_year', $html);
  }

}
