<?php

namespace Drupal\Tests\cp_taxonomy\ExistingSite;

use Drupal\views\Views;

/**
 * Class TaxonomyTermsViewFieldTest.
 *
 * @group cp
 * @group kernel
 *
 * @package Drupal\Tests\cp_taxonomy\ExistingSite
 */
class TaxonomyTermsViewFieldTest extends TestBase {

  /**
   * Test publication.
   *
   * @var \Drupal\bibcite_entity\Entity\ReferenceInterface
   */
  private $publication;

  /**
   * Drupal renderer.
   *
   * @var \Drupal\Core\Render\Renderer
   *   Renderer.
   */
  protected $renderer;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $vid = $this->randomMachineName();
    $this->createGroupVocabulary($this->group, $vid, ['node:taxonomy_test_1']);
    $term = $this->createGroupTerm($this->group, $vid, 'Test term 1');
    $this->publication = $this->createReference([
      'field_taxonomy_terms' => [
        $term->id(),
      ],
    ]);
    $this->group->addContent($this->publication, 'group_entity:bibcite_reference');
    $this->renderer = $this->container->get('renderer');
  }

  /**
   * Test entity reference view.
   */
  public function testReferenceTaxonomyTermViewResult() {
    $this->vsiteContextManager->activateVsite($this->group);
    $view = Views::getView('publications');
    $view->setDisplay('page_1');
    $view->preExecute();
    $view->execute();
    $render_view = $view->render();
    $html = $this->renderer->renderPlain($render_view)->__toString();
    $this->assertContains('Test term 1', $html);
  }

}
