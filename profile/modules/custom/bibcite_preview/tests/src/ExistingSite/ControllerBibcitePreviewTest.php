<?php

namespace Drupal\Tests\bibcite_preview\ExistingSite;

use Drupal\bibcite_preview\Controller\BibciteEntityPreviewController;
use Drupal\Tests\openscholar\ExistingSite\OsExistingSiteTestBase;

/**
 * Tests bibcite_preview module controller.
 *
 * @group kernel
 * @group publications
 */
class ControllerBibcitePreviewTest extends OsExistingSiteTestBase {

  /**
   * Reference Entity.
   *
   * @var \Drupal\bibcite_entity\Entity\ReferenceInterface
   */
  protected $reference;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->reference = $this->createReference();
  }

  /**
   * Tests BibciteEntityPreviewController render and title test.
   */
  public function testControllerRenderAndTitle() {
    $controller = new BibciteEntityPreviewController($this->container->get('entity.manager'), $this->container->get('renderer'));
    $build = $controller->view($this->reference);
    $this->assertEquals('bibcite_reference', $build['#entity_type']);
    $this->assertEquals('full', $build['#view_mode']);
    $this->assertEquals($this->reference->id(), $build['#bibcite_reference']->id());

    $build = $controller->view($this->reference, 'citation');
    $this->assertEquals('citation', $build['#view_mode']);

    $title = $controller->title($this->reference);
    $this->assertEquals($this->reference->label(), $title);
  }

}
