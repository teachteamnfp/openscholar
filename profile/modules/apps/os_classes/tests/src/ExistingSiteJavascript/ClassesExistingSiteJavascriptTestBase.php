<?php

namespace Drupal\Tests\os_classes\ExistingSiteJavascript;

use weitzman\DrupalTestTraits\ExistingSiteWebDriverTestBase;

/**
 * Test base for classes tests.
 */
abstract class ClassesExistingSiteJavascriptTestBase extends ExistingSiteWebDriverTestBase {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->entityTypeManager = $this->container->get('entity_type.manager');
  }

  /**
   * Creates a class.
   *
   * @param array $values
   *   (optional) The values used to create the entity.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The created class node.
   */
  protected function createClass(array $values = []) {
    $node = $this->entityTypeManager->getStorage('node')->create($values + [
      'type' => 'class',
      'title' => $this->randomMachineName(),
    ]);
    $node->save();

    return $node;
  }

}
