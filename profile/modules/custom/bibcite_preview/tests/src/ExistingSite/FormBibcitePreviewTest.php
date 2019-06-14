<?php

namespace Drupal\Tests\bibcite_preview\ExistingSite;

use Drupal\bibcite_preview\Form\ReferencePreviewForm;
use Drupal\Core\Form\FormState;
use Drupal\Tests\openscholar\ExistingSite\OsExistingSiteTestBase;

/**
 * Tests bibcite_preview module selector form.
 *
 * @group kernel
 * @group publications
 */
class FormBibcitePreviewTest extends OsExistingSiteTestBase {

  /**
   * Reference Entity.
   *
   * @var \Drupal\bibcite_entity\Entity\ReferenceInterface
   */
  protected $reference;

  /**
   * Reference Preview Form.
   *
   * @var \Drupal\bibcite_preview\Form\ReferencePreviewForm*/
  protected $form;

  /**
   * Form Builder Interface.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface*/
  protected $formBuilder;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->reference = $this->createReference();
    $this->form = new ReferencePreviewForm($this->container->get('entity_display.repository'), $this->container->get('config.factory'));
    $this->formBuilder = $this->container->get('form_builder');
  }

  /**
   * Test form render and simple submit.
   */
  public function testFormRender() {
    $form_state = (new FormState())
      ->setValues([
        'view_mode' => 'citation',
      ]);
    $form = $this->form->buildForm([], $form_state, $this->reference);
    $this->assertNotEmpty($form['backlink']);
    $this->assertNotEmpty($form['uuid']);
    $this->assertEquals($this->reference->uuid(), $form['uuid']['#value']);
    $this->assertNotEmpty($form['view_mode']);
    $this->assertNotEmpty($form['submit']);
    $this->formBuilder->submitForm($this->form, $form_state, $this->reference);
    $this->assertEquals(count($form_state->getErrors()), 0);
    $this->assertNotEmpty($form_state->getValue('uuid'));
    $this->assertEquals($this->reference->uuid(), $form_state->getValue('uuid'));
    $this->assertNotEmpty($form_state->getValue('view_mode'));
    $this->assertEquals('citation', $form_state->getValue('view_mode'));
    // Should assert redirect value, but can't access cause programmed is true.
  }

}
