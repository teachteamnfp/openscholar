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
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->taxonomyWidget = $this->osWidgets->createInstance('taxonomy_widget');
    $this->vocabulary = $this->createVocabulary();
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
