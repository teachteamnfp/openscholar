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

  /**
   * Test all node types.
   */
  public function testAllNodeTypesFieldExists() {
    $field_name = 'field_taxonomy_terms';
    $definitions = \Drupal::entityTypeManager()->getDefinitions();
    $entityManager = \Drupal::service('entity_field.manager');
    foreach ($definitions as $definition) {
      if ($definition->id() == 'node') {
        $bundles = \Drupal::service('entity.manager')
          ->getBundleInfo($definition->id());
        foreach ($bundles as $machine_name => $bundle) {
          $fields = $entityManager->getFieldDefinitions($definition->id(), $machine_name);
          $this->assertArrayHasKey($field_name, $fields, 'Node bundle ' . $bundle['label'] . ' not contains ' . $field_name . ' field.');
        }
      }
    }
  }

}
