<?php

namespace Drupal\Tests\cp_taxonomy\ExistingSite;

use Drupal\Core\Cache\Cache;

/**
 * Class CheckingFieldsTest.
 *
 * @group cp
 * @group kernel
 *
 * @package Drupal\Tests\cp_taxonomy\ExistingSite
 */
class CpTaxonomySettingsTest extends TestBase {

  protected $group;
  protected $configTaxonomy;
  protected $renderer;

  /**
   * The admin user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $groupAdmin;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->renderer = $this->container->get('renderer');
    $configFactory = $this->container->get('config.factory');
    $this->configTaxonomy = $configFactory->getEditable('cp_taxonomy.settings');
    $this->groupAdmin = $this->createUser([], NULL, TRUE);
  }

  /**
   * Test view tags on node entity.
   */
  public function testViewTagsOnPageNodeEntity() {
    $view_builder = $this->entityTypeManager
      ->getViewBuilder('node');

    $vocabulary = $this->createVocabulary();
    $term = $this->createTerm($vocabulary);
    $node = $this->createNode([
      'type' => 'news',
      'field_taxonomy_terms' => [
        $term->id(),
      ],
    ]);
    $this->configTaxonomy->set('display_term_under_content', '1');
    $this->configTaxonomy->save(TRUE);
    $render = $view_builder->view($node, 'full');
    /** @var \Drupal\Core\Render\Markup $markup_array */
    $markup = $this->renderer->renderRoot($render);
    $this->assertContains($term->label(), $markup->__toString());

    $this->configTaxonomy->set('display_term_under_content', '0');
    $this->configTaxonomy->save(TRUE);
    // Invalidate cache for node.
    Cache::invalidateTags($node->getCacheTagsToInvalidate());

    $render = $view_builder->view($node, 'full');
    /** @var \Drupal\Core\Render\Markup $markup_array */
    $markup = $this->renderer->renderRoot($render);
    $this->assertNotContains($term->label(), $markup->__toString());
  }

  /**
   * Test view tags on node entity.
   */
  public function testViewTagsOnListingPageNodeEntity() {
    $view_builder = $this->entityTypeManager
      ->getViewBuilder('node');

    $vocabulary = $this->createVocabulary();
    $term = $this->createTerm($vocabulary);
    $node = $this->createNode([
      'type' => 'news',
      'field_taxonomy_terms' => [
        $term->id(),
      ],
    ]);
    $this->configTaxonomy->set('display_term_under_content_teaser_types', ['node:news']);
    $this->configTaxonomy->save(TRUE);
    $render = $view_builder->view($node, 'teaser');
    /** @var \Drupal\Core\Render\Markup $markup_array */
    $markup = $this->renderer->renderRoot($render);
    $this->assertContains($term->label(), $markup->__toString());

    $this->configTaxonomy->set('display_term_under_content_teaser_types', []);
    $this->configTaxonomy->save(TRUE);
    // Invalidate cache for node.
    Cache::invalidateTags($node->getCacheTagsToInvalidate());

    $render = $view_builder->view($node, 'teaser');
    /** @var \Drupal\Core\Render\Markup $markup_array */
    $markup = $this->renderer->renderRoot($render);
    $this->assertNotContains($term->label(), $markup->__toString());
  }

  /**
   * Test group admin settings form.
   */
  public function testCpSettingsTaxonomyForm() {
    $this->configTaxonomy->set('display_term_under_content', '1');
    $this->configTaxonomy->save(TRUE);

    $this->drupalLogin($this->groupAdmin);
    $this->visit('/' . $this->groupAlias . '/cp/settings/taxonomy');
    $this->assertSession()->statusCodeEquals(200);
    $page = $this->getCurrentPageContent();
    // Assert checkboxes are checked.
    $this->assertContains('name="display_term_under_content" value="1" checked="checked" class="form-checkbox"', $page);
    $this->assertContains('name="display_term_under_content_teaser_types[bibcite_reference:artwork]" value="bibcite_reference:artwork" checked="checked" class="form-checkbox"', $page);

    $edit = [
      'display_term_under_content' => '0',
      'display_term_under_content_teaser_types[bibcite_reference:artwork]' => '',
    ];
    $this->drupalPostForm(NULL, $edit, 'Save configuration');
    $this->assertSession()->statusCodeEquals(200);
    $page = $this->getCurrentPageContent();
    // Assert checkboxes are unchecked.
    $this->assertContains('name="display_term_under_content" value="1" class="form-checkbox"', $page);
    $this->assertContains('name="display_term_under_content_teaser_types[bibcite_reference:artwork]" value="bibcite_reference:artwork" class="form-checkbox"', $page);

  }

}
