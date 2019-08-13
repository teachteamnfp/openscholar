<?php

namespace Drupal\Tests\os_publications\ExistingSite;

/**
 * PublicationsFormTest.
 *
 * @group functional
 * @group publications
 */
class PublicationsFormTest extends TestBase {

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
    $this->drupalLogin($this->groupAdmin);
  }

  /**
   * Tests form alterations for HCA and other styles.
   */
  public function testPublicationFormFields(): void {
    // Tests HCA form fields(Month and Date).
    $this->changeStyle('harvard_chicago_author_date');
    $this->visitViaVsite('bibcite/reference/add/journal_article', $this->group);
    $this->assertSession()->elementExists('css', '#edit-bibcite-year-coded');
    $this->assertSession()->elementExists('css', '#edit-bibcite-year-0-value');
    $this->assertSession()->elementExists('css', '#edit-publication-month');
    $this->assertSession()->elementExists('css', '#edit-publication-day');

    // Test Non HCA(apa) form fields (month and date should not be present)
    $this->changeStyle('apa');
    $this->visitViaVsite('bibcite/reference/add/journal_article', $this->group);
    $this->assertSession()->elementExists('css', '#edit-bibcite-year-coded');
    $this->assertSession()->elementExists('css', '#edit-bibcite-year-0-value');
    $this->assertSession()->elementNotExists('css', '#edit-publication-month');
    $this->assertSession()->elementNotExists('css', '#edit-publication-day');

    // Test form validations for non HCA style.
    $edit = [
      'bibcite_secondary_title[0][value]' => 'TestJournalTitle',
      'bibcite_year[0][value]' => '',
    ];
    $this->submitForm($edit, 'edit-submit');
    $this->assertSession()->pageTextContains('Year is mandatory');

    // Test form validations for HCA style.
    $this->changeStyle('harvard_chicago_author_date');
    $this->visitViaVsite('bibcite/reference/add/journal_article', $this->group);
    // Test that on entering only month and date it throws error.
    $edit = [
      'bibcite_secondary_title[0][value]' => 'TestJournalTitle',
      'bibcite_year[0][value]' => '',
      'publication_month' => '5',
      'publication_day' => '12',
    ];
    $this->submitForm($edit, 'edit-submit');
    $this->assertSession()->elementExists('css', '.form-item--error-message');
    $this->assertSession()->pageTextContains('Year is mandatory');

    // Test for month and day validation.
    $edit = [
      'bibcite_secondary_title[0][value]' => 'TestJournalTitle',
      'bibcite_year[0][value]' => '2019',
      'publication_month' => '4',
      'publication_day' => '31',
    ];
    $this->submitForm($edit, 'edit-submit');
    $this->assertSession()->elementExists('css', '.form-item--error-message');
    $this->assertSession()->pageTextContains('Date must be valid');

    // Test a positive case/successful form submission.
    $edit = [
      'bibcite_secondary_title[0][value]' => 'TestJournalTitle',
      'bibcite_year[0][value]' => '2019',
      'publication_month' => '4',
      'publication_day' => '15',
    ];
    $this->submitForm($edit, 'edit-submit');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('4/15/2019');
  }

  /**
   * Tests years display on node and publication views.
   *
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testPublicationYearDisplay() : void {
    // Test publication year display for HCA style with all 3 fields on node and
    // view pages.
    $this->changeStyle('harvard_chicago_author_date');
    $reference = $this->createReference([
      'html_title' => 'Journal title test',
      'bibcite_secondary_title' => 'TestJournalTitle',
      'bibcite_year' => '2019',
      'publication_month' => '4',
      'publication_day' => '15',
    ]
    );
    $this->group->addContent($reference, 'group_entity:bibcite_reference');
    $this->visitViaVsite('bibcite/reference/' . $reference->id(), $this->group);
    $this->assertSession()->pageTextContains('4/15/2019');
    $this->visitViaVsite('publications', $this->group);
    $this->assertSession()->pageTextContains('4/15/2019');

    // Test publication year display for HCA style with year and month fields.
    $this->visitViaVsite('bibcite/reference/' . $reference->id() . '/edit', $this->group);
    $edit = [
      'bibcite_year[0][value]' => '2019',
      'publication_month' => '4',
      'publication_day' => '_none',
    ];
    $this->submitForm($edit, 'edit-submit');
    $this->assertSession()->pageTextContains('4/2019');
    $this->visitViaVsite('publications', $this->group);
    $this->assertSession()->pageTextContains('4/2019');

    // Test publication year display for HCA style with year and day fields.
    $this->visitViaVsite('bibcite/reference/' . $reference->id() . '/edit', $this->group);
    $edit = [
      'bibcite_year[0][value]' => '1980',
      'publication_month' => '_none',
      'publication_day' => '4',
    ];
    $this->submitForm($edit, 'edit-submit');
    $this->assertSession()->pageTextNotContains('4/1980');
    $this->assertSession()->pageTextContains('1980');
    $this->visitViaVsite('publications', $this->group);
    $this->assertSession()->pageTextNotContains('4/1980');
    $this->assertSession()->pageTextContains('1980');
  }

  /**
   * Tests year text display on node and view pages.
   *
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testPublicationYearTextDisplay() : void {
    // Test publication year text display on node page.
    $reference1 = $this->createReference([
      'html_title' => 'Journal title test',
      'bibcite_secondary_title' => 'TestJournalTitle',
      'bibcite_year' => '10010',
      'bibcite_year_coded' => '10010',
    ]
    );
    $this->group->addContent($reference1, 'group_entity:bibcite_reference');
    $this->visitViaVsite('bibcite/reference/' . $reference1->id(), $this->group);
    $this->assertSession()->pageTextNotContains('10010');
    $this->assertSession()->pageTextContains('Submitted');

    $reference2 = $this->createReference([
      'html_title' => 'Journal title test two',
      'bibcite_secondary_title' => 'TestJournalTitletwo',
      'bibcite_year' => '10030',
      'bibcite_year_coded' => '10030',
    ]
    );
    $this->group->addContent($reference2, 'group_entity:bibcite_reference');
    $this->visitViaVsite('bibcite/reference/' . $reference2->id(), $this->group);
    $this->assertSession()->pageTextNotContains('10030');
    $this->assertSession()->pageTextContains('In Press');

    // Test year text headers display properly on view page.
    $this->visitViaVsite('publications/year', $this->group);
    $this->assertSession()->pageTextContains('Submitted');
    $this->assertSession()->pageTextContains('In Press');
    $page = $this->getCurrentPage()->getContent();
    $this->assertContains('<h3>Submitted</h3>', $page);
    $this->assertContains('<h3>In Press</h3>', $page);
  }

}
