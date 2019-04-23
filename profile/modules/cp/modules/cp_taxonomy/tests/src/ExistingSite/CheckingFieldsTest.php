<?php

namespace Drupal\Tests\cp_taxonomy\ExistingSite;

/**
 * Class CheckingFieldsTest.
 *
 * @group other
 * @group kernel
 *
 * @package Drupal\Tests\cp_taxonomy\ExistingSite
 */
class CheckingFieldsTest extends TestBase {

  private $fieldName = 'field_taxonomy_terms';

  /**
   * Test all node types.
   */
  public function testAllNodeTypesFieldExists() {
    $definitions = \Drupal::entityTypeManager()->getDefinitions();
    $entityManager = \Drupal::service('entity_field.manager');
    foreach ($definitions as $definition) {
      if ($definition->id() == 'node') {
        $bundles = \Drupal::service('entity.manager')
          ->getBundleInfo($definition->id());
        foreach ($bundles as $machine_name => $bundle) {
          $fields = $entityManager->getFieldDefinitions($definition->id(), $machine_name);
          $this->assertArrayHasKey($this->fieldName, $fields, 'Node bundle ' . $bundle['label'] . ' not contains ' . $this->fieldName . ' field.');
        }
      }
    }
  }

  /**
   * Test all media types.
   */
  public function testAllMediaTypesFieldExists() {
    $definitions = \Drupal::entityTypeManager()->getDefinitions();
    $entityManager = \Drupal::service('entity_field.manager');
    foreach ($definitions as $definition) {
      if ($definition->id() == 'media') {
        $bundles = \Drupal::service('entity.manager')
          ->getBundleInfo($definition->id());
        foreach ($bundles as $machine_name => $bundle) {
          $fields = $entityManager->getFieldDefinitions($definition->id(), $machine_name);
          $this->assertArrayHasKey($this->fieldName, $fields, 'Media bundle ' . $bundle['label'] . ' not contains ' . $this->fieldName . ' field.');
        }
      }
    }
  }

}
