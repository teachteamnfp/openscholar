<?php

namespace Drupal\Tests\os_publications\ExistingSite;

use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Class PublicationsFormTest.
 *
 * @group functional
 *
 * @package Drupal\Tests\os_publications\ExistingSite
 */
class PublicationsFormTest extends ExistingSiteBase {

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

    $this->drupalGet('cp/settings/publications');
    drupal_flush_all_caches();
    $this->drupalGet('cp/settings/publications');
    $this->assertSession()->statusCodeEquals(200);
  }

  /**
   * Test Settings form.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testPublicationSettingsForm() {
    $this->drupalLogin($this->user);
    $this->drupalGet('cp/settings/publications');
    drupal_flush_all_caches();
    $this->drupalGet('cp/settings/publications');
    // Testing multiple form fields.
    $edit = [
      'os_publications_preferred_bibliographic_format' => 'apa',
      'edit-os-publications-filter-publication-types-artwork' => 'artwork',
      'edit-biblio-sort' => 'title',
      'edit-os-publications-shorten-citations' => 'checked',
    ];
    $this->submitForm($edit, 'edit-submit');
    $this->assertSession()->checkboxChecked('edit-os-publications-preferred-bibliographic-format-apa');
    $this->assertSession()->checkboxChecked('filter_publication_types[artwork]');
    $this->assertSession()->checkboxChecked('edit-os-publications-shorten-citations');
    $this->assertSession()->fieldValueEquals('edit-biblio-sort', 'title');
  }

}
