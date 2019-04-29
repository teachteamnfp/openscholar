<?php

namespace Drupal\Tests\os_publications\ExistingSite;

use Drupal\Tests\openscholar\ExistingSite\OsExistingSiteTestBase;

/**
 * Class PublicationsFormTest.
 *
 * @group functional
 *
 * @package Drupal\Tests\os_publications\ExistingSite
 */
class PublicationsFormTest extends OsExistingSiteTestBase {

  /**
   * Group administrator.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $groupAdmin;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->groupAdmin = $this->createUser();
    $this->addGroupAdmin($this->groupAdmin, $this->group);
  }

  /**
   * Test Setting form route.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function testPublicationSettingsPath(): void {
    $this->drupalLogin($this->groupAdmin);

    $this->drupalGet("{$this->group->get('path')->first()->getValue()['alias']}cp/settings/publications");
    $this->assertSession()->statusCodeEquals(200);
  }

  /**
   * Test Settings form.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function testPublicationSettingsForm() {
    $this->drupalLogin($this->groupAdmin);
    $this->drupalGet("{$this->group->get('path')->first()->getValue()['alias']}cp/settings/publications");
    // Testing multiple form fields.
    $edit = [
      'os_publications_preferred_bibliographic_format' => 'apa',
      'edit-os-publications-filter-publication-types-artwork' => 'artwork',
      'edit-biblio-sort' => 'title',
      'edit-os-publications-shorten-citations' => 'checked',
    ];
    $this->submitForm($edit, 'edit-submit');
    $this->assertSession()->checkboxChecked('edit-os-publications-preferred-bibliographic-format-apa');
    $this->assertSession()->checkboxChecked('os_publications_filter_publication_types[artwork]');
    $this->assertSession()->checkboxChecked('edit-os-publications-shorten-citations');
    $this->assertSession()->fieldValueEquals('edit-biblio-sort', 'title');
  }

}
