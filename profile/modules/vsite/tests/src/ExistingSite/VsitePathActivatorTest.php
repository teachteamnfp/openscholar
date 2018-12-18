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
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->entityTypeManager = $this->container->get('entity_type.manager');
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
    $this->createGroup([
      'type' => 'personal',
      'path' => [
        'alias' => '/test-alias',
      ],
    ]);

    $this->drupalGet('/test-alias');
    $this->assertSession()->statusCodeEquals(200);
  }

}
