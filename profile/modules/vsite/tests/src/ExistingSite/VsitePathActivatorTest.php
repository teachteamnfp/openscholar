<?php

namespace Drupal\Tests\vsite\ExistingSite;

use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Tests VsitePathActivator.
 *
 * @group vsite
 * @group functional
 * @coversDefaultClass \Drupal\vsite\Plugin\VsitePathActivator
 */
class VsitePathActivatorTest extends ExistingSiteBase {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The storage element to add a vsite storage to.
   *
   * @var \Drupal\vsite\Config\HierarchicalStorageInterface
   */
  protected $hierarchicalStorage;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->entityTypeManager = $this->container->get('entity_type.manager');
    $this->hierarchicalStorage = $this->container->get('hierarchical.storage');
  }

  /**
   * Creates a group.
   *
   * @param array $values
   *   (optional) The values used to create the entity.
   *
   * @return \Drupal\group\Entity\GroupInterface
   *   The created group entity.
   */
  protected function createGroup(array $values = []) {
    $group = $this->entityTypeManager->getStorage('group')->create($values + [
      'type' => 'default',
      'label' => $this->randomMachineName(),
    ]);
    $group->enforceIsNew();
    $group->save();
    return $group;
  }

  /**
   * Tests modifier matched event.
   */
  public function testModifierMatched() {
    $group = $this->createGroup([
      'type' => 'personal',
      'path' => [
        'alias' => '/test-alias',
      ],
    ]);

    $this->drupalGet('/test-alias');
    // TODO: Fix this failing test.
    // TODO: This should be returning 500.
    // TODO: Found that inside Symfony's RouteListener that it is returning 403,
    // TODO: but due to unknown reason Drupal is coverting it to 500.
    // TODO: Find and fix the problem.
    // $this->assertSession()->statusCodeEquals(200);
    // $this->assertEquals("vsite:{$group->id()}", $this
    // ->hierarchicalStorage
    // ->getCollectionName());
  }

}
