<?php

namespace Drupal\Tests\cp_taxonomy\ExistingSiteJavascript;

use Drupal\Core\Cache\Cache;
use Drupal\taxonomy\Entity\Vocabulary;

/**
 * Tests cache invalidation listing and entity page.
 *
 * @group functional
 * @group cp
 */
class CpTaxonomyCacheTest extends CpTaxonomyExistingSiteJavascriptTestBase {

  protected $term;
  protected $config;

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
      'media:taxonomy_test_file',
      'bibcite_reference:artwork',
    ];
    $this->createGroupVocabulary($this->group, 'vocab_group_1', $allowed_types);
    $this->term = $this->createGroupTerm($this->group, 'vocab_group_1', 'Term1');
    $this->vsiteContextManager->activateVsite($this->group);
    $this->config = $this->container->get('config.factory')->getEditable('cp_taxonomy.settings');
  }

  /**
   * Test node listing and page caching.
   */
  public function testNodeListingAndPageCaching() {
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
   * Set config, show terms on entity pages.
   */
  private function showTermsOnPage() {
    $this->config->set('display_term_under_content', TRUE);
    $this->config->save(TRUE);
  }

  /**
   * Set config, hide terms on entity pages.
   */
  private function hideTermsOnPage() {
    $this->config->set('display_term_under_content', FALSE);
    $this->config->save(TRUE);
  }

  /**
   * Set config, show terms on entity listing.
   *
   * @param array $types
   *   Allowed types.
   */
  private function showTermsOnListing(array $types = []) {
    $this->config->set('display_term_under_content_teaser_types', $types);
    $this->config->save(TRUE);
  }

  /**
   * Set config, hide terms on entity listing.
   */
  private function hideTermsOnListing() {
    $this->config->set('display_term_under_content_teaser_types', []);
    $this->config->save(TRUE);
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
