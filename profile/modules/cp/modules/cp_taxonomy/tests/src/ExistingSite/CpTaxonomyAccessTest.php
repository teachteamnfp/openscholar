<?php

namespace Drupal\Tests\cp_taxonomy\ExistingSite;

/**
 * Class CpTaxonomyAccessTest.
 *
 * @package Drupal\Tests\cp_taxonomy\ExistingSite
 *
 * @group functional
 * @group cp
 */
class CpTaxonomyAccessTest extends TestBase {

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
  protected $groupAdmin;

  /**
   * Entity Type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  protected $member;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->group = $this->createGroup();
    $this->groupAdmin = $this->createUser();
    $this->member = $this->createUser();
    $this->addGroupAdmin($this->groupAdmin, $this->group);
    $this->group->addMember($this->member);
    $this->entityTypeManager = $this->container->get('entity_type.manager');
  }

  /**
   * Tests all necessary operations and buttons are available to permitted role.
   *
   * @covers ::cp_taxonomy_taxonomy_vocabulary_access
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testCpVocabularyAccess(): void {
    $vid = 'test_vocab';
    $this->createGroupVocabulary($this->group, $vid, ['node:taxonomy_test_1']);

    // Test positive case on vocabulary list page.
    $this->drupalLogin($this->groupAdmin);
    $this->visitViaVsite('cp/taxonomy', $this->group);
    $this->assertSession()->linkExists('List terms');
    $this->assertSession()->linkExists('Edit vocabulary');
    $this->assertSession()->linkExists('Add terms');

    $this->drupalLogout();

    // Test negative case on vocabulary list page.
    $this->drupalLogin($this->member);
    $this->visitViaVsite('cp/taxonomy', $this->group);
    $this->assertSession()->statusCodeEquals(403);
  }

  /**
   * Tests all necessary operations and buttons are available to permitted role.
   *
   * @covers ::cp_taxonomy_taxonomy_term_access
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testCpTermAccess(): void {
    $vid = 'test_vocab';
    $this->createGroupVocabulary($this->group, $vid, ['node:taxonomy_test_1']);
    $this->createGroupTerm($this->group, $vid, ['name' => 'Aterm']);
    $this->createGroupTerm($this->group, $vid, ['name' => 'Bterm']);;

    // Test positive case on term overview page.
    $this->drupalLogin($this->groupAdmin);
    $this->visitViaVsite('cp/taxonomy/' . $vid, $this->group);
    $this->assertSession()->linkExists('Aterm');
    $this->assertSession()->linkExists('Bterm');
    $this->assertSession()->linkExists('Edit');
    $this->assertSession()->linkExists('Delete');
    $this->assertSession()->elementExists('css', '.draggable');
    $this->assertSession()->elementExists('css', '#edit-submit');
    $this->assertSession()->elementExists('css', '#edit-reset-alphabetical');

    $this->drupalLogout();

    // Test negative case on term overview list page.
    $this->drupalLogin($this->member);
    $this->visitViaVsite('cp/taxonomy/' . $vid, $this->group);
    $this->assertSession()->statusCodeEquals(403);
  }

}
