<?php

namespace Drupal\Tests\cp_taxonomy\ExistingSiteJavascript;

use Drupal\Core\Cache\Cache;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\Tests\openscholar\Traits\CpTaxonomyTestTrait;

/**
 * Tests taxonomy visibility on listing pages.
 *
 * @group functional
 * @group cp
 */
class CpTaxonomyListingTest extends CpTaxonomyExistingSiteJavascriptTestBase {

  use CpTaxonomyTestTrait;

  protected $term;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $admin = $this->createAdminUser();
    $this->addGroupAdmin($admin, $this->group);
    $this->drupalLogin($admin);
    $allowed_types = [
      'node:faq',
      'media:*',
      'bibcite_reference:*',
    ];
    $this->createGroupVocabulary($this->group, 'vocab_group_1', $allowed_types);
    $this->term = $this->createGroupTerm($this->group, 'vocab_group_1', ['name' => 'Term1']);
    $this->vsiteContextManager->activateVsite($this->group);
  }

  /**
   * Test node faq listing.
   */
  public function testNodeFaqListing() {
    $web_assert = $this->assertSession();
    $node = $this->createNode([
      'type' => 'faq',
      'field_taxonomy_terms' => [
        $this->term->id(),
      ],
      'status' => 1,
    ]);
    $this->group->addContent($node, 'group_node:faq');

    // Test listing on faq.
    $this->showTermsOnListing(['node:faq']);
    $this->visitViaVsite("faq", $this->group);
    $web_assert->statusCodeEquals(200);
    $this->assertContainTaxonomyTermOnPage();
    Cache::invalidateTags(['entity-with-taxonomy-terms:' . $this->group->id()]);
    $this->hideTermsOnListing();
    $this->visitViaVsite("faq", $this->group);
    $web_assert->statusCodeEquals(200);
    $this->assertNotContainTaxonomyTermOnPage();
  }

  /**
   * Test publications listing.
   */
  public function testPublicationsListing() {
    $web_assert = $this->assertSession();
    $publication = $this->createReference([
      'field_taxonomy_terms' => [
        $this->term->id(),
      ],
    ]);
    $this->group->addContent($publication, 'group_entity:bibcite_reference');

    // Test listing.
    $this->showTermsOnListing(['bibcite_reference:*']);
    $this->visitViaVsite("publications", $this->group);
    $web_assert->statusCodeEquals(200);
    $web_assert->pageTextContains($this->term->label());
    Cache::invalidateTags(['entity-with-taxonomy-terms:' . $this->group->id()]);
    $this->hideTermsOnListing();
    $this->visitViaVsite("publications", $this->group);
    $web_assert->statusCodeEquals(200);
    $web_assert->pageTextNotContains($this->term->label());
  }

  /**
   * Assert contains term on page even if not visible.
   */
  protected function assertContainTaxonomyTermOnPage() {
    $page = $this->getCurrentPage();
    $term_html = $page->find('css', '.field--name-field-taxonomy-terms')->getHtml();
    $this->assertContains($this->term->label(), $term_html);
  }

  /**
   * Assert not contains term on page even if not visible.
   */
  protected function assertNotContainTaxonomyTermOnPage() {
    $page = $this->getCurrentPage();
    $this->isNull($page->find('css', '.field--name-field-taxonomy-terms'));
  }

  /**
   * {@inheritdoc}
   */
  public function tearDown() {
    $this->vsiteContextManager->activateVsite($this->group);
    $vocabulary = Vocabulary::load('vocab_group_1');
    $vocabulary->delete();
    parent::tearDown();
  }

}
