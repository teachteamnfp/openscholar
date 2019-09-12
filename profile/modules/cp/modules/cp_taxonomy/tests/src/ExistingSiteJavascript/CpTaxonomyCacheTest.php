<?php

namespace Drupal\Tests\cp_taxonomy\ExistingSiteJavascript;

use Drupal\Core\Cache\Cache;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\Tests\openscholar\Traits\CpTaxonomyTestTrait;

/**
 * Tests cache invalidation listing and entity page.
 *
 * @group functional-javascript
 * @group cp
 */
class CpTaxonomyCacheTest extends CpTaxonomyExistingSiteJavascriptTestBase {

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
      'node:news',
      'media:*',
      'bibcite_reference:*',
    ];
    $this->createGroupVocabulary($this->group, 'vocab_group_1', $allowed_types);
    $this->term = $this->createGroupTerm($this->group, 'vocab_group_1', ['name' => 'Term1']);
    $this->vsiteContextManager->activateVsite($this->group);
  }

  /**
   * Test node page caching.
   */
  public function testNodePageCaching() {
    $web_assert = $this->assertSession();
    $node = $this->createNode([
      'type' => 'news',
      'field_taxonomy_terms' => [
        $this->term->id(),
      ],
      'status' => 1,
    ]);
    $this->group->addContent($node, 'group_node:news');

    // Test page.
    $this->showTermsOnPage();
    $this->visitViaVsite("node/" . $node->id(), $this->group);
    $web_assert->statusCodeEquals(200);
    $web_assert->pageTextContains($this->term->label());
    Cache::invalidateTags(['entity-with-taxonomy-terms:' . $this->group->id()]);
    $this->hideTermsOnPage();
    $this->visitViaVsite("node/" . $node->id(), $this->group);
    $web_assert->statusCodeEquals(200);
    $web_assert->pageTextNotContains($this->term->label());
  }

  /**
   * Test node listing caching.
   */
  public function testNodeListingCaching() {
    $web_assert = $this->assertSession();
    $node = $this->createNode([
      'type' => 'news',
      'field_taxonomy_terms' => [
        $this->term->id(),
      ],
      'status' => 1,
    ]);
    $this->group->addContent($node, 'group_node:news');

    // Test listing.
    $this->showTermsOnListing(['node:news']);
    $this->visitViaVsite("news", $this->group);
    $web_assert->statusCodeEquals(200);
    $web_assert->pageTextContains($this->term->label());
    Cache::invalidateTags(['entity-with-taxonomy-terms:' . $this->group->id()]);
    $this->hideTermsOnListing();
    $this->visitViaVsite("news", $this->group);
    $web_assert->statusCodeEquals(200);
    $web_assert->pageTextNotContains($this->term->label());
  }

  /**
   * Test publication page caching.
   */
  public function testPublicationPageCaching() {
    $web_assert = $this->assertSession();
    $publication = $this->createReference([
      'field_taxonomy_terms' => [
        $this->term->id(),
      ],
    ]);
    $this->group->addContent($publication, 'group_entity:bibcite_reference');

    // Test page.
    $this->showTermsOnPage();
    $this->visitViaVsite("bibcite/reference/" . $publication->id(), $this->group);
    $web_assert->statusCodeEquals(200);
    $web_assert->pageTextContains($this->term->label());
    Cache::invalidateTags(['entity-with-taxonomy-terms:' . $this->group->id()]);
    $this->hideTermsOnPage();
    $this->visitViaVsite("bibcite/reference/" . $publication->id(), $this->group);
    $web_assert->statusCodeEquals(200);
    $web_assert->pageTextNotContains($this->term->label());
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
