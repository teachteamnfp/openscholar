<?php

namespace Drupal\Tests\cp_taxonomy\ExistingSite;

use Drupal\Core\Cache\Cache;

/**
 * Class CpTaxonomySettingsTest.
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
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->renderer = $this->container->get('renderer');
    $configFactory = $this->container->get('config.factory');
    $this->configTaxonomy = $configFactory->getEditable('cp_taxonomy.settings');
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
  public function testViewTagsOnListingNodeEntity() {
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
   * Test title view mode.
   */
  public function testTitleViewModeHidden() {
    $view_builder = $this->entityTypeManager
      ->getViewBuilder('node');

    $vocabulary = $this->createVocabulary();
    $term = $this->createTerm($vocabulary);
    $node = $this->createNode([
      'type' => 'person',
      'field_taxonomy_terms' => [
        $term->id(),
      ],
    ]);
    $this->configTaxonomy->set('display_term_under_content_teaser_types', ['node:person']);
    $this->configTaxonomy->save(TRUE);
    $render = $view_builder->view($node, 'title');
    /** @var \Drupal\Core\Render\Markup $markup_array */
    $markup = $this->renderer->renderRoot($render);
    $this->assertNotContains($term->label(), $markup->__toString());
  }

}
