<?php

namespace Drupal\Tests\cp_taxonomy\ExistingSite;

use Behat\Mink\Exception\Exception;
use Drupal\Tests\vsite\ExistingSite\VsiteExistingSiteTestBase;

/**
 * Test everything related to the taxonomy forms.
 *
 * @package Drupal\Tests\cp_taxonomy\ExistingSite
 * @group functional
 */
class CpTaxonomyTest extends VsiteExistingSiteTestBase {

  /**
   * The Group object for the site.
   *
   * @var \Drupal\group\Entity\GroupInterface
   */
  protected $group;

  /**
   * The PURL of the site.
   *
   * @var string
   */
  protected $groupAlias;

  /**
   * The admin user we're testing as.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $admin;

  /**
   * The GroupMember object.
   *
   * @var \Drupal\group\GroupMembership
   */
  protected $groupMember;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->groupAlias = $this->getRandomGenerator()->name();

    $this->group = $this->createGroup([
      'path' => [
        'alias' => '/' . $this->groupAlias,
      ],
    ]);

    $this->admin = $this->createUser([], NULL, TRUE);
    $this->group->addMember($this->admin);
    $this->groupMember = $this->group->getMember($this->admin);
  }

  /**
   * Test everything one piece at a time.
   */
  public function testTaxonomyFunctionality() {
    try {
      $this->assertTrue(\Drupal::moduleHandler()->moduleExists('cp_taxonomy'));
      $this->drupalLogin($this->admin);
      $this->visit('/cp/taxonomy');
      $this->assertSession()->statusCodeEquals(403);

      $this->visit('/' . $this->groupAlias);
      $this->assertSession()->statusCodeEquals(200);

      // The form loads on vsites.
      $this->visit('/' . $this->groupAlias . '/cp/taxonomy');
      $this->assertSession()->statusCodeEquals(200);
      $this->assertSession()->pageTextContains('No vocabularies available.');

      // Adding a new vocab.
      $this->clickLink('Add vocabulary', 1);
      $this->assertContains($this->groupAlias . '/cp/taxonomy/add', $this->getUrl());
      $vocabName = strtolower($this->getRandomGenerator()->name());
      $this->getCurrentPage()->fillField('Name', $vocabName);
      $this->getCurrentPage()->fillField('Machine-readable name', $vocabName);
      $this->getCurrentPage()->pressButton('Save');
      $this->assertContains('/' . $this->groupAlias . '/cp/taxonomy', $this->getUrl());
      $this->assertNotContains('/' . $this->groupAlias . '/cp/taxonoyomy/add', $this->getUrl());

      // Editing the vocab.
      $this->clickLink('Edit vocabulary');
      $this->assertContains($this->groupAlias . '/cp/taxonomy/' . $vocabName . '/edit', $this->getUrl());
      $this->getCurrentPage()->fillField('Description', 'aaa unique value zzz');
      $this->getCurrentPage()->pressButton('Save');
      $this->assertContains('/' . $this->groupAlias . '/cp/taxonomy', $this->getUrl());
      $this->assertNotContains('/' . $this->groupAlias . '/cp/taxonomy/' . $vocabName . '/edit', $this->getUrl());

      // Term list.
      $this->clickLink('List terms');
      $this->assertContains($this->groupAlias . '/cp/taxonomy/' . $vocabName, $this->getUrl());
      $this->assertSession()->pageTextContains('No terms available');

      // Adding a term.
      $this->clickLink('Add term');
      $this->assertContains($this->groupAlias . '/cp/taxonomy/' . $vocabName . '/add', $this->getUrl());
      $termName = $this->getRandomGenerator()->name();
      $this->getCurrentPage()->fillField('Name', $termName);
      $this->getCurrentPage()->pressButton('Save');
      $this->assertContains('/' . $this->groupAlias . '/' . $vocabName . '/' . strtolower($termName), $this->getUrl());

      // Edit form is vsite-spaced.
      // Nothing has changed on this form so nothing about it needs testing.
      $this->visit('/' . $this->groupAlias . '/cp/taxonomy/' . $vocabName);
      $this->assertSession()->pageTextContains($termName);

      $terms = taxonomy_term_load_multiple_by_name($termName);
      /** @var \Drupal\taxonomy\Entity\Term $term */
      $term = reset($terms);
      $this->assertSession()->linkByHrefExists('/' . $this->groupAlias . '/taxonomy/term/' . $term->id() . '/edit');
      $this->assertSession()->linkByHrefExists('/' . $this->groupAlias . '/taxonomy/term/' . $term->id() . '/delete');

      // Deleting the vocab.
      $this->visit('/' . $this->groupAlias . '/cp/taxonomy');
      $this->clickLink('Edit vocabulary');
      $this->click('#edit-delete');
      $this->submitForm([], 'Delete');
      $this->assertContains($this->groupAlias . '/cp/taxonomy', $this->getUrl());
      $this->assertSession()->pageTextContains('No vocabularies available.');
    }
    catch (Exception $e) {
      file_put_contents(REQUEST_TIME . '.txt', $this->getCurrentPage()->getContent());
      $this->fail($e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    }

  }

}
