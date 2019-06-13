<?php

namespace Drupal\Tests\os_classes\Traits;

use Drupal\Core\Entity\EntityInterface;

/**
 * OsClassesTest helpers.
 */
trait OsClassesTestTrait {

  /**
   * Creates a class.
   *
   * @param array $values
   *   (optional) The values used to create the entity.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The created class node.
   */
  protected function createClass(array $values = []): EntityInterface {
    $entity_type_manager = $this->container->get('entity_type.manager');
    $node = $entity_type_manager->getStorage('node')->create($values + [
      'type' => 'class',
      'title' => $this->randomMachineName(),
    ]);
    $node->save();

    return $node;
  }

}
