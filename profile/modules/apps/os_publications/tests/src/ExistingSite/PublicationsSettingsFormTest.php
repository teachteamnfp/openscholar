<?php

namespace Drupal\Tests\os_publications\ExistingSite;

/**
 * Class PublicationsSettingsFormTest.
 *
 * @group functional
 * @group publications
 *
 * @package Drupal\Tests\os_publications\ExistingSite
 */
class PublicationsSettingsFormTest extends TestBase {

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

    $this->drupalGet("{$this->group->get('path')->first()->getValue()['alias']}/cp/settings/publications");
    $this->assertSession()->statusCodeEquals(200);
  }

  /**
   * Test Settings form.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function testPublicationSettingsForm(): void {
    $this->drupalLogin($this->groupAdmin);
    $this->drupalGet("{$this->group->get('path')->first()->getValue()['alias']}/cp/settings/publications");
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

  /**
   * Tests Note in teaser setting.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testNoteInTeaserSetting(): void {
    $this->drupalLogin($this->groupAdmin);
    $reference = $this->createReference([
      'notes' => [
        'value' => 'This is nootes test.',
      ],
    ]);
    $this->group->addContent($reference, 'group_entity:bibcite_reference');
    $this->visitViaVsite('cp/settings/publications', $this->group);

    // Test short note does not appear.
    $edit = [
      'os_publications_note_in_teaser' => FALSE,
    ];
    $this->submitForm($edit, 'edit-submit');
    $this->visitViaVsite('publications', $this->group);
    $this->assertSession()->elementNotExists('css', '.field--name-notes');

    // Test short note appears.
    $this->visitViaVsite('cp/settings/publications', $this->group);
    $edit = [
      'os_publications_note_in_teaser' => TRUE,
    ];
    $this->submitForm($edit, 'edit-submit');
    $this->visitViaVsite('publications', $this->group);
    $this->assertSession()->elementExists('css', '.field--name-notes');
  }

}
