<?php

namespace Drupal\Tests\vsite\ExistingSite;

use Drupal\vsite\Event\VsiteActivatedEvent;
use Drupal\vsite\VsiteEvents;
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
   * A test user with group creation rights.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $groupCreator;

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
    $this->entityTypeManager = $this->container->get('entity_type.manager');
    $this->hierarchicalStorage = $this->container->get('hierarchical.storage');
    $this->eventDispatcher = $this->container->get('event_dispatcher');
    $this->groupCreator = $this->createUser([
      'view the administration theme',
      'access administration pages',
      'access group overview',
      'create personal group',
    ]);
    $this->drupalLogin($this->groupCreator);
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
   *
   * @covers \Drupal\vsite\Plugin\VsitePathActivator::onModifierMatched
   */
  public function testModifierMatched() {
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
  public function testOnRequest() {
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
