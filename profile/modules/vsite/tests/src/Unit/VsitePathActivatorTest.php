<?php

namespace Drupal\Tests\vsite\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\vsite\Plugin\VsitePathActivator;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Tests for the VsitePathActivator class.
 *
 * @group vsite
 * @group unit
 * @coversDefaultClass \Drupal\vsite\Plugin\VsitePathActivator
 */
class VsitePathActivatorTest extends UnitTestCase {

  /**
   * Dependency injection container.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerBuilder
   */
  protected $container;

  /**
   * The object we're testing.
   *
   * @var \Drupal\vsite\Plugin\VsitePathActivator
   */
  protected $vsitePathActivator;

  /**
   * Mock for the VsiteContextManager object.
   *
   * @var \PHPUnit_Framework_MockObject_MockObject
   */
  protected $vsiteContextManager;

  /**
   * Mock for EntityTypeManager.
   *
   * @var \PHPUnit_Framework_MockObject_MockObject
   */
  protected $entityTypeManager;

  /**
   * Mock for a node we're pretending to view.
   *
   * @var \PHPUnit_Framework_MockObject_MockObject
   */
  protected $node;

  /**
   * Mock for the group we're in.
   *
   * @var \PHPUnit_Framework_MockObject_MockObject
   */
  protected $group;

  /**
   * Set up steps needed for the tests.
   *
   * Sets up a node-group relationship to be referred to later.
   */
  public function setUp() {
    parent::setUp();

    $this->container = new ContainerBuilder();
    \Drupal::setContainer($this->container);

    $this->vsiteContextManager = $this->createMock('\Drupal\vsite\Plugin\VsiteContextManager');
    // Needed mocks:
    // entity type manager
    // group entity storage interface
    // group
    // group content storage interface
    // group content
    // node
    // current route match.
    $this->entityTypeManager = $this->createMock('\Drupal\Core\Entity\EntityTypeManagerInterface');

    $this->node = $this->createMock('\Drupal\node\NodeInterface');
    $this->group = $this->createMock('\Drupal\group\Entity\GroupInterface');
    $group_content = $this->createMock('\Drupal\group\Entity\GroupContentInterface');
    $group_content->method('getGroup')
      ->willReturn($this->group);
    $group_content->method('getEntity')
      ->willReturn($this->node);

    $groupStorage = $this->createMock('\Drupal\Core\Entity\EntityStorageInterface');
    $groupStorage->method('load')
      ->with(1)
      ->willReturn($this->group);

    $groupContentStorage = $this->createMock('\Drupal\group\Entity\Storage\GroupContentStorageInterface');
    $groupContentStorage->method('load')
      ->with(1)
      ->willReturn($group_content);
    $groupContentStorage->method('loadByEntity')
      ->with($this->node)
      ->willReturn([$group_content]);

    $this->entityTypeManager->method('getStorage')
      ->will($this->returnCallback(function ($arg) use ($groupStorage, $groupContentStorage) {
        switch ($arg) {
          case 'group':
            return $groupStorage;

          case 'group_content':
            return $groupContentStorage;

          default:
            return NULL;
        }
      }));

    $this->vsitePathActivator = new VsitePathActivator($this->vsiteContextManager, $this->entityTypeManager);
  }

  /**
   * Test that a vsite is activated when a purl modifier is matched.
   */
  public function testModifierMatched() {

    $currentRouteMatch = $this->createMock('\Drupal\Core\Routing\CurrentRouteMatch');
    $currentRouteMatch->method('getParameter')
      ->with('node')
      ->willReturn($this->node);
    $this->container->set('current_route_match', $currentRouteMatch);

    $event = $this->createMock('\Drupal\purl\Event\ModifierMatchedEvent');
    $event->method('getValue')
      ->willReturn(1);

    $this->vsiteContextManager->expects($this->at(0))
      ->method('activateVsite')
      ->with($this->group);

    $this->vsitePathActivator->onModifierMatched($event);

  }

  /**
   * Test that a vsite is activated when on the group entity path.
   */
  public function testOnRequest() {

    $currentRouteMatch = $this->createMock('\Drupal\Core\Routing\CurrentRouteMatch');
    $currentRouteMatch->expects($this->at(0))
      ->method('getParameter')
      ->with('group')
      ->willReturn($this->group);
    $this->container->set('current_route_match', $currentRouteMatch);

    $event = $this->createMock('\Symfony\Component\HttpKernel\Event\GetResponseEvent');

    $this->vsiteContextManager->expects($this->at(0))
      ->method('activateVsite')
      ->with($this->group);

    $this->vsitePathActivator->onRequest($event);
  }

  /**
   * Tests that we can fetch a group by its node param.
   *
   * This must be separate because PathActivator caches the request match.
   */
  public function testGetGroupFromNode() {
    $currentRouteMatch = $this->createMock('\Drupal\Core\Routing\CurrentRouteMatch');
    $currentRouteMatch->expects($this->at(0))
      ->method('getParameter')
      ->with('group')
      ->willReturn(NULL);
    $currentRouteMatch->expects($this->at(3))
      ->method('getParameter')
      ->with('node')
      ->willReturn($this->node);
    $this->container->set('current_route_match', $currentRouteMatch);

    $this->assertEquals($this->group, $this->vsitePathActivator->getGroupFromRoute());
  }

  /**
   * No vsite tests.
   *
   * Test that the nothing will happen when neither group nor node are
   *   parameters of the route match.
   */
  public function testNoGroupFromRoute() {
    $currentRouteMatch = $this->createMock('\Drupal\Core\Routing\CurrentRouteMatch');
    $currentRouteMatch->expects($this->any())
      ->method('getParameter')
      ->willReturn(NULL);
    $this->container->set('current_route_match', $currentRouteMatch);

    $this->assertNull($this->vsitePathActivator->getGroupFromRoute());
  }

}
