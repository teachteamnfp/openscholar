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
      'type' => 'artwork',
      'field_taxonomy_terms' => [
        $term->id(),
      ],
    ]);
    $this->group->addContent($this->publication, 'group_entity:bibcite_reference');
    $this->renderer = $this->container->get('renderer');
    $this->vsiteContextManager->activateVsite($this->group);
  }

  /**
   * Test taxonomy terms view field show.
   */
  public function testTaxonomyTermViewFieldShow() {
    $config = $this->configFactory->getEditable('cp_taxonomy.settings');
    // Make sure to show bundle.
    $config->set('display_term_under_content_teaser_types', ['bibcite_reference:artwork']);
    $config->save(TRUE);

    $view = Views::getView('publications');
    $view->setDisplay('page_1');
    $view->preExecute();
    $view->execute();
    $render_view = $view->render();
    $html = $this->renderer->renderPlain($render_view)->__toString();
    $this->assertContains('Test term 1', $html);
  }

  /**
   * Test taxonomy terms view field hide.
   */
  public function testTaxonomyTermViewFieldHide() {
    $config = $this->configFactory->getEditable('cp_taxonomy.settings');
    // Make sure to hide all bundle.
    $config->set('display_term_under_content_teaser_types', ['bibcite_reference:not_exist_bundle']);
    $config->save(TRUE);

    $view = Views::getView('publications');
    $view->setDisplay('page_1');
    $view->preExecute();
    $view->execute();
    $render_view = $view->render();
    $html = $this->renderer->renderPlain($render_view)->__toString();
    $this->assertNotContains('Test term 1', $html);
  }

}
