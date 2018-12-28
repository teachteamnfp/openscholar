<?php

namespace Drupal\Tests\vsite\ExistingSite;

use Drupal\vsite\Event\VsiteActivatedEvent;
use Drupal\vsite\VsiteEvents;

/**
 * Tests VsiteStorageDefinition.
 *
 * @group vsite
 * @group kernel
 * @coversDefaultClass \Drupal\vsite\Config\VsiteStorageDefinition
 */
class VsiteStorageDefinitionTest extends VsiteExistingSiteTestBase {

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
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->eventDispatcher = $this->container->get('event_dispatcher');
    $this->hierarchicalStorage = $this->container->get('hierarchical.storage');
    $this->group = $this->createGroup([
      'type' => 'personal',
    ]);
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
