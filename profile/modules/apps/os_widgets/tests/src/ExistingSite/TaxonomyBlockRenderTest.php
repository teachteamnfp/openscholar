<?php

namespace Drupal\Tests\os_widgets\ExistingSite;

/**
 * Class TaxonomyWidget.
 *
 * @group kernel
 * @group widgets
 * @covers \Drupal\os_widgets\Plugin\OsWidgets\TaxonomyWidget
 */
class TaxonomyBlockRenderTest extends OsWidgetsExistingSiteTestBase {

  /**
   * The object we're testing.
   *
   * @var \Drupal\os_widgets\Plugin\OsWidgets\TaxonomyWidget
   */
  protected $taxonomyWidget;

  /**
   * Test vocabulary.
   *
   * @var \Drupal\taxonomy\Entity\Vocabulary
   */
  protected $vocabulary;

  /**
   * Config.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->taxonomyWidget = $this->osWidgets->createInstance('taxonomy_widget');
    $this->vocabulary = $this->createVocabulary();
    $this->config = $this->container->get('config.factory');
    // Reset vocabulary allowed values.
    $config_vocab = $this->config->getEditable('taxonomy.vocabulary.' . $this->vocabulary->id());
    $config_vocab->set('allowed_vocabulary_reference_types', [])
      ->save(TRUE);
  }

  /**
   * Test basic listing test without count.
   */
  public function testBuildListingTaxonomyTermsWithoutCount() {
    $term1 = $this->createTerm($this->vocabulary, ['name' => 'Lorem1']);
    $term2 = $this->createTerm($this->vocabulary, ['name' => 'Lorem2']);

    $block_content = $this->createBlockContent([
      'type' => 'taxonomy',
      'field_taxonomy_vocabulary' => [
        $this->vocabulary->id(),
      ],
      'field_taxonomy_show_count' => 0,
    ]);
    $view_builder = $this->entityTypeManager
      ->getViewBuilder('block_content');
    $render = $view_builder->view($block_content);
    $renderer = $this->container->get('renderer');

    /** @var \Drupal\Core\Render\Markup $markup_array */
    $markup = $renderer->renderRoot($render);
    $this->assertContains($term1->label(), $markup->__toString());
    $this->assertNotContains($term1->label() . ' (0)', $markup->__toString());
    $this->assertContains($term2->label(), $markup->__toString());
    $this->assertNotContains($term2->label() . ' (0)', $markup->__toString());
  }

  /**
   * Test empty term without count if show count is enabled.
   */
  public function testBuildTaxonomyTermsWithoutCountEnableShowCount() {
    $term = $this->createTerm($this->vocabulary, ['name' => 'Lorem1']);

    $block_content = $this->createBlockContent([
      'type' => 'taxonomy',
      'field_taxonomy_vocabulary' => [
        $this->vocabulary->id(),
      ],
      'field_taxonomy_show_count' => 1,
    ]);
    $view_builder = $this->entityTypeManager
      ->getViewBuilder('block_content');
    $render = $view_builder->view($block_content);
    $renderer = $this->container->get('renderer');

    /** @var \Drupal\Core\Render\Markup $markup_array */
    $markup = $renderer->renderRoot($render);
    $this->assertContains($term->label(), $markup->__toString());
    $this->assertNotContains($term->label() . ' (0)', $markup->__toString());
  }

  /**
   * Test listing test with depth.
   */
  public function testBuildListingTaxonomyTermsWithDepth() {
    $term1 = $this->createTerm($this->vocabulary, ['name' => 'Lorem1']);
    $term2 = $this->createTerm($this->vocabulary, ['name' => 'Lorem2', 'parent' => $term1->id()]);

    $block_content = $this->createBlockContent([
      'type' => 'taxonomy',
      'field_taxonomy_vocabulary' => [
        $this->vocabulary->id(),
      ],
      'field_taxonomy_tree_depth' => [
        1,
      ],
    ]);
    $view_builder = $this->entityTypeManager
      ->getViewBuilder('block_content');
    $render = $view_builder->view($block_content);
    $renderer = $this->container->get('renderer');

    /** @var \Drupal\Core\Render\Markup $markup_array */
    $markup = $renderer->renderRoot($render);
    $this->assertContains($term1->label(), $markup->__toString());
    $this->assertNotContains($term2->label(), $markup->__toString());

    $block_content = $this->createBlockContent([
      'type' => 'taxonomy',
      'field_taxonomy_vocabulary' => [
        $this->vocabulary->id(),
      ],
      'field_taxonomy_tree_depth' => [
        2,
      ],
    ]);
    $view_builder = $this->entityTypeManager
      ->getViewBuilder('block_content');
    $render = $view_builder->view($block_content);
    $renderer = $this->container->get('renderer');

    /** @var \Drupal\Core\Render\Markup $markup_array */
    $markup = $renderer->renderRoot($render);
    $this->assertContains($term1->label(), $markup->__toString());
    $this->assertContains($term2->label(), $markup->__toString());
  }

  /**
   * Test listing test unchecked show children.
   */
  public function testBuildListingTaxonomyTermsUncheckedShowChildren() {
    $term1 = $this->createTerm($this->vocabulary, ['name' => 'Lorem1']);
    $term2 = $this->createTerm($this->vocabulary, ['name' => 'Lorem2', 'parent' => $term1->id()]);

    $block_content = $this->createBlockContent([
      'type' => 'taxonomy',
      'field_taxonomy_vocabulary' => [
        $this->vocabulary->id(),
      ],
      'field_taxonomy_tree_depth' => [
        2,
      ],
      'field_taxonomy_show_children' => [
        0,
      ],
    ]);
    $view_builder = $this->entityTypeManager
      ->getViewBuilder('block_content');
    $render = $view_builder->view($block_content);
    $renderer = $this->container->get('renderer');

    /** @var \Drupal\Core\Render\Markup $markup_array */
    $markup = $renderer->renderRoot($render);
    $this->assertContains($term1->label(), $markup->__toString());
    $this->assertNotContains($term2->label(), $markup->__toString());
  }

  /**
   * Test listing with max number on top level.
   */
  public function testBuildListingWithMaxNumberTopLevel() {
    $term1 = $this->createTerm($this->vocabulary, ['name' => 'Lorem1']);
    $term2 = $this->createTerm($this->vocabulary, ['name' => 'Lorem2']);
    $term3 = $this->createTerm($this->vocabulary, ['name' => 'Lorem3', 'parent' => $term2->id()]);
    $term4 = $this->createTerm($this->vocabulary, ['name' => 'Lorem4', 'parent' => $term2->id()]);
    $term5 = $this->createTerm($this->vocabulary, ['name' => 'Lorem5']);

    $block_content = $this->createBlockContent([
      'type' => 'taxonomy',
      'field_taxonomy_vocabulary' => [
        $this->vocabulary->id(),
      ],
      'field_taxonomy_range' => [
        2,
      ],
    ]);
    $view_builder = $this->entityTypeManager
      ->getViewBuilder('block_content');
    $render = $view_builder->view($block_content);
    $renderer = $this->container->get('renderer');

    /** @var \Drupal\Core\Render\Markup $markup_array */
    $markup = $renderer->renderRoot($render);
    $this->assertContains($term1->label(), $markup->__toString());
    $this->assertContains($term2->label(), $markup->__toString());
    $this->assertContains($term3->label(), $markup->__toString());
    $this->assertContains($term4->label(), $markup->__toString());
    $this->assertNotContains($term5->label(), $markup->__toString());
  }

  /**
   * Test listing with max number and offset on top level.
   */
  public function testBuildListingWithMaxNumberAndOffsetTopLevel() {
    $term1 = $this->createTerm($this->vocabulary, ['name' => 'Lorem1']);
    $term2 = $this->createTerm($this->vocabulary, ['name' => 'Lorem2', 'parent' => $term1->id()]);
    $term3 = $this->createTerm($this->vocabulary, ['name' => 'Lorem3']);
    $term4 = $this->createTerm($this->vocabulary, ['name' => 'Lorem4', 'parent' => $term3->id()]);
    $term5 = $this->createTerm($this->vocabulary, ['name' => 'Lorem5']);
    $term6 = $this->createTerm($this->vocabulary, ['name' => 'Lorem6']);

    $block_content = $this->createBlockContent([
      'type' => 'taxonomy',
      'field_taxonomy_vocabulary' => [
        $this->vocabulary->id(),
      ],
      'field_taxonomy_offset' => [
        1,
      ],
      'field_taxonomy_range' => [
        2,
      ],
    ]);
    $view_builder = $this->entityTypeManager
      ->getViewBuilder('block_content');
    $render = $view_builder->view($block_content);
    $renderer = $this->container->get('renderer');

    /** @var \Drupal\Core\Render\Markup $markup_array */
    $markup = $renderer->renderRoot($render);
    $this->assertNotContains($term1->label(), $markup->__toString());
    $this->assertNotContains($term2->label(), $markup->__toString());
    $this->assertContains($term3->label(), $markup->__toString());
    $this->assertContains($term4->label(), $markup->__toString());
    $this->assertContains($term5->label(), $markup->__toString());
    $this->assertNotContains($term6->label(), $markup->__toString());
  }

  /**
   * Test listing description visibility.
   */
  public function testBuildListingDescriptionVisibility() {
    $term1 = $this->createTerm($this->vocabulary, ['name' => 'Lorem1']);

    // Hide description.
    $block_content = $this->createBlockContent([
      'type' => 'taxonomy',
      'field_taxonomy_vocabulary' => [
        $this->vocabulary->id(),
      ],
      'field_taxonomy_show_term_desc' => [
        0,
      ],
    ]);
    $view_builder = $this->entityTypeManager
      ->getViewBuilder('block_content');
    $render = $view_builder->view($block_content);
    $renderer = $this->container->get('renderer');

    $description = $term1->getDescription();
    /** @var \Drupal\Core\Render\Markup $markup_array */
    $markup = $renderer->renderRoot($render);
    $this->assertNotContains($description, $markup->__toString());

    // Show description.
    $block_content = $this->createBlockContent([
      'type' => 'taxonomy',
      'field_taxonomy_vocabulary' => [
        $this->vocabulary->id(),
      ],
      'field_taxonomy_show_term_desc' => [
        1,
      ],
    ]);
    $view_builder = $this->entityTypeManager
      ->getViewBuilder('block_content');
    $render = $view_builder->view($block_content);
    $renderer = $this->container->get('renderer');

    $description = $term1->getDescription();
    /** @var \Drupal\Core\Render\Markup $markup_array */
    $markup = $renderer->renderRoot($render);
    $this->assertContains($description, $markup->__toString());
  }

  /**
   * Test taxonomy tree parent visible.
   */
  public function testBuildTreeParentVisible() {
    $config_vocab = $this->config->getEditable('taxonomy.vocabulary.' . $this->vocabulary->id());
    $config_vocab
      ->set('allowed_vocabulary_reference_types', [
        'node:taxonomy_test_1',
      ])
      ->save(TRUE);
    $term1 = $this->createTerm($this->vocabulary, ['name' => 'Lorem1']);
    $term2 = $this->createTerm($this->vocabulary, ['name' => 'Lorem2', 'parent' => $term1->id()]);
    $term3 = $this->createTerm($this->vocabulary, ['name' => 'Lorem3', 'parent' => $term2->id()]);
    // Use cp_taxonomy_test's content type.
    $this->createNode([
      'type' => 'taxonomy_test_1',
      'field_taxonomy_terms' => [
        $term3->id(),
      ],
    ]);

    // Set bundle to search taxonomy_test_1 bundle.
    $block_content = $this->createBlockContent([
      'type' => 'taxonomy',
      'field_taxonomy_vocabulary' => [
        $this->vocabulary->id(),
      ],
      'field_taxonomy_bundle' => [
        'taxonomy_test_1',
      ],
    ]);
    $view_builder = $this->entityTypeManager
      ->getViewBuilder('block_content');
    $render = $view_builder->view($block_content);
    $renderer = $this->container->get('renderer');

    /** @var \Drupal\Core\Render\Markup $markup_array */
    $markup = $renderer->renderRoot($render);
    $this->assertContains($term1->label(), $markup->__toString());
    $this->assertContains($term2->label(), $markup->__toString());
    $this->assertContains($term3->label(), $markup->__toString());
  }

  /**
   * Test taxonomy tree child visible.
   */
  public function testBuildTreeChildHiddenOnShowEmptyTerms() {
    $config_vocab = $this->config->getEditable('taxonomy.vocabulary.' . $this->vocabulary->id());
    $config_vocab
      ->set('allowed_vocabulary_reference_types', [
        'node:taxonomy_test_1',
      ])
      ->save(TRUE);
    $term1 = $this->createTerm($this->vocabulary, ['name' => 'Lorem1']);
    $term2 = $this->createTerm($this->vocabulary, ['name' => 'Lorem2', 'parent' => $term1->id()]);
    $term3 = $this->createTerm($this->vocabulary, ['name' => 'Lorem3', 'parent' => $term2->id()]);
    // Use cp_taxonomy_test's content type.
    $this->createNode([
      'type' => 'taxonomy_test_1',
      'field_taxonomy_terms' => [
        $term2->id(),
      ],
    ]);

    // Set bundle but show empty terms.
    $block_content = $this->createBlockContent([
      'type' => 'taxonomy',
      'field_taxonomy_vocabulary' => [
        $this->vocabulary->id(),
      ],
      'field_taxonomy_bundle' => [
        'taxonomy_test_1',
      ],
      'field_taxonomy_show_empty_terms' => [
        1,
      ],
    ]);
    $view_builder = $this->entityTypeManager
      ->getViewBuilder('block_content');
    $render = $view_builder->view($block_content);
    $renderer = $this->container->get('renderer');

    /** @var \Drupal\Core\Render\Markup $markup_array */
    $markup = $renderer->renderRoot($render);
    $this->assertContains($term1->label(), $markup->__toString());
    $this->assertContains($term2->label(), $markup->__toString());
    $this->assertContains($term3->label(), $markup->__toString());
  }

  /**
   * Test taxonomy tree child visible.
   */
  public function testBuildTreeChildVisibleOnDisableShowEmptyTerms() {
    $config_vocab = $this->config->getEditable('taxonomy.vocabulary.' . $this->vocabulary->id());
    $config_vocab
      ->set('allowed_vocabulary_reference_types', [
        'node:taxonomy_test_1',
      ])
      ->save(TRUE);
    $term1 = $this->createTerm($this->vocabulary, ['name' => 'Lorem1']);
    $term2 = $this->createTerm($this->vocabulary, ['name' => 'Lorem2', 'parent' => $term1->id()]);
    $term3 = $this->createTerm($this->vocabulary, ['name' => 'Lorem3', 'parent' => $term2->id()]);
    // Use cp_taxonomy_test's content type.
    $this->createNode([
      'type' => 'taxonomy_test_1',
      'field_taxonomy_terms' => [
        $term2->id(),
      ],
    ]);

    // Set bundle and hide empty terms.
    $block_content = $this->createBlockContent([
      'type' => 'taxonomy',
      'field_taxonomy_vocabulary' => [
        $this->vocabulary->id(),
      ],
      'field_taxonomy_bundle' => [
        'taxonomy_test_1',
      ],
      'field_taxonomy_show_empty_terms' => [
        0,
      ],
    ]);
    $view_builder = $this->entityTypeManager
      ->getViewBuilder('block_content');
    $render = $view_builder->view($block_content);
    $renderer = $this->container->get('renderer');

    /** @var \Drupal\Core\Render\Markup $markup_array */
    $markup = $renderer->renderRoot($render);
    $this->assertContains($term1->label(), $markup->__toString());
    $this->assertContains($term2->label(), $markup->__toString());
    $this->assertNotContains($term3->label(), $markup->__toString());
  }

  /**
   * Test entity status published.
   */
  public function testBuildEntityStatusPublished() {
    $config_vocab = $this->config->getEditable('taxonomy.vocabulary.' . $this->vocabulary->id());
    $config_vocab
      ->set('allowed_vocabulary_reference_types', [
        'node:taxonomy_test_1',
        'media:taxonomy_test_file',
        'bibcite_reference:artwork',
      ])
      ->save(TRUE);
    $term = $this->createTerm($this->vocabulary, ['name' => 'Lorem1']);
    // Create nodes.
    $this->createNode([
      'type' => 'taxonomy_test_1',
      'status' => 1,
      'field_taxonomy_terms' => [
        $term->id(),
      ],
    ]);
    $this->createNode([
      'type' => 'taxonomy_test_1',
      'status' => 0,
      'field_taxonomy_terms' => [
        $term->id(),
      ],
    ]);
    // Create media.
    $this->createMedia([
      'bundle' => 'taxonomy_test_file',
      'status' => 1,
      'field_taxonomy_terms' => [
        $term->id(),
      ],
    ]);
    $this->createMedia([
      'bundle' => 'taxonomy_test_file',
      'status' => 0,
      'field_taxonomy_terms' => [
        $term->id(),
      ],
    ]);
    // Create publications.
    $this->createReference([
      'status' => 1,
      'field_taxonomy_terms' => [
        $term->id(),
      ],
    ]);
    $this->createReference([
      'status' => 0,
      'field_taxonomy_terms' => [
        $term->id(),
      ],
    ]);

    // Show count.
    $block_content = $this->createBlockContent([
      'type' => 'taxonomy',
      'field_taxonomy_vocabulary' => [
        $this->vocabulary->id(),
      ],
      'field_taxonomy_show_count' => 1,
    ]);
    $view_builder = $this->entityTypeManager
      ->getViewBuilder('block_content');
    $render = $view_builder->view($block_content);
    $renderer = $this->container->get('renderer');

    /** @var \Drupal\Core\Render\Markup $markup_array */
    $markup = $renderer->renderRoot($render);
    // Checking only 3 entities related, not 6.
    $this->assertContains($term->label() . ' (3)', $markup->__toString());
  }

  /**
   * Test hidden show count on term.
   */
  public function testBuildHiddenShowCount() {
    $config_vocab = $this->config->getEditable('taxonomy.vocabulary.' . $this->vocabulary->id());
    $config_vocab
      ->set('allowed_vocabulary_reference_types', [
        'node:taxonomy_test_1',
      ])
      ->save(TRUE);
    $term = $this->createTerm($this->vocabulary, ['name' => 'Lorem1']);
    // Create nodes.
    $this->createNode([
      'type' => 'taxonomy_test_1',
      'status' => 1,
      'field_taxonomy_terms' => [
        $term->id(),
      ],
    ]);
    $this->createNode([
      'type' => 'taxonomy_test_1',
      'status' => 1,
      'field_taxonomy_terms' => [
        $term->id(),
      ],
    ]);

    // Show count disabled.
    $block_content = $this->createBlockContent([
      'type' => 'taxonomy',
      'field_taxonomy_vocabulary' => [
        $this->vocabulary->id(),
      ],
      'field_taxonomy_show_count' => 0,
    ]);
    $view_builder = $this->entityTypeManager
      ->getViewBuilder('block_content');
    $render = $view_builder->view($block_content);
    $renderer = $this->container->get('renderer');

    /** @var \Drupal\Core\Render\Markup $markup_array */
    $markup = $renderer->renderRoot($render);
    $this->assertContains($term->label(), $markup->__toString());
    $this->assertNotContains($term->label() . ' (2)', $markup->__toString());
  }

  /**
   * Test display type values.
   */
  public function testBuildDisplayTypeValuesTheme() {
    // Display type menu.
    $block_content = $this->createBlockContent([
      'type' => 'taxonomy',
      'field_taxonomy_vocabulary' => [
        $this->vocabulary->id(),
      ],
      'field_taxonomy_display_type' => 'menu',
    ]);
    $view_builder = $this->entityTypeManager
      ->getViewBuilder('block_content');
    $render = $view_builder->view($block_content);

    $this->assertSame('os_widgets_taxonomy_display_type_menu', $render['taxonomy']['terms']['#theme']);

    // Display type menu.
    $block_content = $this->createBlockContent([
      'type' => 'taxonomy',
      'field_taxonomy_vocabulary' => [
        $this->vocabulary->id(),
      ],
      'field_taxonomy_display_type' => 'slider',
    ]);
    $view_builder = $this->entityTypeManager
      ->getViewBuilder('block_content');
    $render = $view_builder->view($block_content);

    $this->assertSame('os_widgets_taxonomy_display_type_slider', $render['taxonomy']['terms']['#theme']);
  }

  /**
   * Test behavior values.
   */
  public function testBuildBehaviorValuesTheme() {
    $term = $this->createTerm($this->vocabulary, ['name' => 'Lorem1']);
    // Create node.
    $this->createNode([
      'type' => 'taxonomy_test_1',
      'status' => 1,
      'field_taxonomy_terms' => [
        $term->id(),
      ],
    ]);
    // Behavior value select.
    $block_content = $this->createBlockContent([
      'type' => 'taxonomy',
      'field_taxonomy_vocabulary' => [
        $this->vocabulary->id(),
      ],
      'field_taxonomy_bundle' => 'node:taxonomy_test_1',
      'field_taxonomy_behavior' => 'select',
    ]);
    $view_builder = $this->entityTypeManager
      ->getViewBuilder('block_content');
    $render = $view_builder->view($block_content);
    $renderer = $this->container->get('renderer');

    /** @var \Drupal\Core\Render\Markup $markup_array */
    $markup = $renderer->renderRoot($render);
    // Checking rendered term.
    $this->assertContains($term->label(), $markup->__toString());
  }

  /**
   * {@inheritdoc}
   */
  protected function createBlockContent(array $values = []) {
    // Add default required fields.
    $values += [
      'field_taxonomy_behavior' => ['--all--'],
      'field_taxonomy_vocabulary' => [$this->vocabulary->id()],
      'field_taxonomy_tree_depth' => [0],
      'field_taxonomy_display_type' => ['classic'],
    ];
    return parent::createBlockContent($values);
  }

}
