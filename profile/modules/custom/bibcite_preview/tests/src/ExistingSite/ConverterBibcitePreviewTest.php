<?php

namespace Drupal\Tests\bibcite_preview\ExistingSite;

use Drupal\bibcite_entity\Form\ReferenceForm;
use Drupal\bibcite_preview\ParamConverter\BibciteReferencePreviewConverter;
use Drupal\Core\Form\FormState;
use Drupal\Tests\openscholar\ExistingSite\OsExistingSiteTestBase;

/**
 * Tests bibcite_preview module convert.
 *
 * @group functional
 * @group publications
 */
class ConverterBibcitePreviewTest extends OsExistingSiteTestBase {

  /**
   * Reference Entity.
   *
   * @var \Drupal\bibcite_entity\Entity\ReferenceInterface
   */
  protected $reference;

  /**
   * Bibcite Reference Preview Converter.
   *
   * @var \Drupal\bibcite_preview\ParamConverter\BibciteReferencePreviewConverter
   */
  protected $paramConverter;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->drupalLogin($this->createAdminUser());
    $this->reference = $this->createReference();
    /** @var \Drupal\Core\TempStore\PrivateTempStoreFactory $storage */
    $storage = $this->container->get('tempstore.private');
    /** @var \Drupal\Core\TempStore\PrivateTempStore $store */
    $store = $storage->get('bibcite_reference_preview');

    // Create an EntityForm to handle store entity in form object.
    $form_state = new FormState();
    $form = new ReferenceForm($this->container->get('entity.repository'), $this->container->get('entity_type.bundle.info'), $this->container->get('datetime.time'));
    $form->setEntity($this->reference);
    $form_state->setFormObject($form);

    // Store this form_state, what contains our reference entity.
    $store->set($this->reference->uuid(), $form_state);

    $this->paramConverter = new BibciteReferencePreviewConverter($storage);
  }

  /**
   * Test param converter.
   */
  public function testBibciteReferenceParamConverterConvert() {
    $definition = [
      'type' => 'bibcite_reference_preview',
      'converter' => 'bibcite_reference_preview',
    ];
    $defaults = [];
    $entity = $this->paramConverter->convert($this->reference->uuid(), $definition, 'bibcite_reference_preview', $defaults);
    // Assert to convert can ready entity from our form_state.
    $this->assertNotNull($entity);
    $this->assertEquals($this->reference->uuid(), $entity->uuid());
  }

  /**
   * Test param applies.
   */
  public function testBibciteReferenceParamConverterApplies() {
    $definition = [
      'type' => 'bibcite_reference_preview',
    ];
    /** @var \Drupal\Core\Routing\RouteProvider $route_provider */
    $route_provider = $this->container->get('router.route_provider');
    $route = $route_provider->getRouteByName('entity.bibcite_reference.preview');
    $is_apply = $this->paramConverter->applies($definition, 'bibcite_reference_preview', $route);
    $this->assertTrue($is_apply);
  }

}
