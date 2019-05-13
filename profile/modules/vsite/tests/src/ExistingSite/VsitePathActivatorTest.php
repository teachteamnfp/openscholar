<?php

namespace Drupal\Tests\vsite\ExistingSite;

use Drupal\vsite\Event\VsiteActivatedEvent;
use Drupal\vsite\VsiteEvents;

/**
 * Tests VsitePathActivator.
 *
 * @group functional
 * @group vsite
 * @coversDefaultClass \Drupal\vsite\Plugin\VsitePathActivator
 */
class VsitePathActivatorTest extends VsiteExistingSiteTestBase {

  /**
   * The storage element to add a vsite storage to.
   *
   * @var \Drupal\vsite\Config\HierarchicalStorageInterface
   */
  protected $hierarchicalStorage;

  /**
   * Event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->hierarchicalStorage = $this->container->get('hierarchical.storage');
    $this->eventDispatcher = $this->container->get('event_dispatcher');
  }

  /**
   * Tests modifier matched event.
   *
   * @covers \Drupal\vsite\Plugin\VsitePathActivator::onModifierMatched
   */
  public function testModifierMatched(): void {
    $group = $this->createGroup([
      'type' => 'personal',
      'path' => [
        'alias' => '/test-alias',
      ],
    ]);

    $this->drupalGet('/test-alias/node/add/link');
    $event = new VsiteActivatedEvent($group);
    $this->eventDispatcher->dispatch(VsiteEvents::VSITE_ACTIVATED, $event);
    $this->assertEquals("vsite:{$group->id()}", $this->hierarchicalStorage->getCollectionName());
  }

  /**
   * Tests on request event subscriber.
   *
   * @covers \Drupal\vsite\Plugin\VsitePathActivator::onRequest
   */
  public function testOnRequest(): void {
    $group = $this->createGroup([
      'type' => 'personal',
      'path' => [
        'alias' => '/test-alias',
      ],
    ]);

    $this->drupalGet('<front>');
    $this->assertNotEquals("vsite:{$group->id()}", $this->hierarchicalStorage->getCollectionName());

    $this->drupalGet('/test-alias');
    $event = new VsiteActivatedEvent($group);
    $this->eventDispatcher->dispatch(VsiteEvents::VSITE_ACTIVATED, $event);
    $this->assertEquals("vsite:{$group->id()}", $this->hierarchicalStorage->getCollectionName());
  }

}
