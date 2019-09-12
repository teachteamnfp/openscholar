<?php

namespace Drupal\Tests\cp_taxonomy\ExistingSite;

/**
 * Class CpTaxonomyVocabResetFormTest.
 *
 * @package Drupal\Tests\cp_taxonomy\ExistingSite
 *
 * @group functional
 * @group cp
 */
class CpTaxonomyVocabResetFormTest extends TestBase {

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

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->group = $this->createGroup();
    $this->groupAdmin = $this->createUser();
    $this->addGroupAdmin($this->groupAdmin, $this->group);
    $this->drupalLogin($this->groupAdmin);
    $this->entityTypeManager = $this->container->get('entity_type.manager');
  }

  /**
   * Test Cp Vocabulary Reset Form.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testCpVocabularyResetForm(): void {
    $vid = 'test_vocab';
    $this->createGroupVocabulary($this->group, $vid, ['node:taxonomy_test_1']);
    $term1 = $this->createGroupTerm($this->group, $vid, ['name' => 'Aterm']);
    $term2 = $this->createGroupTerm($this->group, $vid, ['name' => 'Bterm']);
    $term3 = $this->createGroupTerm($this->group, $vid, ['name' => 'Cterm']);

    // Set weights.
    $term1->setWeight(3)->save();
    $term2->setWeight(1)->save();
    $term3->setWeight(2)->save();

    $term_storage = $this->entityTypeManager->getStorage('taxonomy_term');
    $term_storage->resetCache();
    $tree = $term_storage->loadTree($vid);
    $this->assertEquals($term2->id(), $tree[0]->tid, 'Term 2 was moved above Term 1.');
    $this->assertEquals($term3->id(), $tree[1]->tid, 'Term 3 was moved above Term 1.');
    $this->assertEquals($term1->id(), $tree[2]->tid, 'Term 1 was moved below Term 3.');

    // Reset alphabetical via Reset Form.
    $this->visitViaVsite('cp/taxonomy/' . $vid, $this->group);
    $this->submitForm([], 'edit-reset-alphabetical');
    $this->submitForm([], 'edit-submit');

    $term_storage->resetCache();
    $tree = $term_storage->loadTree($vid, 0, NULL, TRUE);
    $this->assertEquals($term1->id(), $tree[0]->id(), 'Term 1 was moved above Term 2.');
    $this->assertEquals($term2->id(), $tree[1]->id(), 'Term 2 was moved below Term 1.');
    $this->assertEquals($term3->id(), $tree[2]->id(), 'Term 3 was moved below Term 2.');

    // Test Cancel Link.
    $url = $this->getSession()->getCurrentUrl();
    $this->submitForm([], 'edit-reset-alphabetical');
    $this->getCurrentPage()->clickLink('Cancel');
    $current_url = $this->getSession()->getCurrentUrl();
    $this->assertEquals($url, $current_url);

  }

}
