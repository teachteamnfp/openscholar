<?php

namespace Drupal\Tests\cp_taxonomy\ExistingSite;

/**
 * Class CheckingFieldsTest.
 *
 * @group cp
 * @group kernel
 *
 * @package Drupal\Tests\cp_taxonomy\ExistingSite
 */
class CheckingFieldsTest extends TestBase {

  private $fieldName = 'field_taxonomy_terms';
  protected $entityFieldManager;
  protected $entityManager;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->entityFieldManager = $this->container->get('entity_field.manager');
    $this->entityManager = $this->container->get('entity.manager');
  }

  /**
   * Test all node types.
   */
  public function testAllNodeTypesFieldExists() {
    $definitions = $this->entityTypeManager->getDefinitions();
    foreach ($definitions as $definition) {
      if ($definition->id() == 'node') {
        $bundles = $this->entityManager->getBundleInfo($definition->id());
        foreach ($bundles as $machine_name => $bundle) {
          $fields = $this->entityFieldManager->getFieldDefinitions($definition->id(), $machine_name);
          $this->assertArrayHasKey($this->fieldName, $fields, 'Node bundle ' . $bundle['label'] . ' not contains ' . $this->fieldName . ' field.');
        }
      }
    }
  }

  /**
   * Test all media types.
   */
  public function testAllMediaTypesFieldExists() {
    $definitions = $this->entityTypeManager->getDefinitions();
    foreach ($definitions as $definition) {
      if ($definition->id() == 'media') {
        $bundles = $this->entityManager->getBundleInfo($definition->id());
        foreach ($bundles as $machine_name => $bundle) {
          $fields = $this->entityFieldManager->getFieldDefinitions($definition->id(), $machine_name);
          $this->assertArrayHasKey($this->fieldName, $fields, 'Media bundle ' . $bundle['label'] . ' not contains ' . $this->fieldName . ' field.');
        }
      }
    }
  }

  /**
   * Test all reference types.
   */
  public function testAllReferenceTypesFieldExists() {
    $definitions = $this->entityTypeManager->getDefinitions();
    foreach ($definitions as $definition) {
      if ($definition->id() == 'bibcite_reference') {
        $bundles = $this->entityManager->getBundleInfo($definition->id());
        foreach ($bundles as $machine_name => $bundle) {
          $fields = $this->entityFieldManager->getFieldDefinitions($definition->id(), $machine_name);
          $this->assertArrayHasKey($this->fieldName, $fields, 'Reference bundle ' . $bundle['label'] . ' not contains ' . $this->fieldName . ' field.');
        }
      }
    }
  }

}
