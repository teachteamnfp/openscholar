<?php

namespace Drupal\Tests\vsite\Kernel;

use Drupal\vsite\Event\VsiteActivatedEvent;
use Drupal\vsite\VsiteEvents;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Tests VsiteStorageDefinition.
 *
 * @group vsite
 * @group kernel
 * @coversDefaultClass \Drupal\vsite\Config\VsiteStorageDefinition
 */
class VsiteStorageDefinitionTest extends ExistingSiteBase {

  /**
   * Event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * Hierarchical storage.
   *
   * @var \Drupal\vsite\Config\HierarchicalStorageInterface
   */
  protected $hierarchicalStorage;

  /**
   * Test group.
   *
   * @var \Drupal\group\Entity\GroupInterface
   */
  protected $group;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

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
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->eventDispatcher = $this->container->get('event_dispatcher');
    $this->hierarchicalStorage = $this->container->get('hierarchical.storage');
    $this->entityTypeManager = $this->container->get('entity_type.manager');

    $this->group = $this->createGroup();
  }

  /**
   * Tests hierarchical storage update on vsite activation.
   */
  public function testVsiteStorageUpdate() {
    $event = new VsiteActivatedEvent($this->group);

    $this->eventDispatcher->dispatch(VsiteEvents::VSITE_ACTIVATED, $event);

    $this->assertEquals("vsite:{$this->group->id()}", $this->hierarchicalStorage->getCollectionName());
  }

}
