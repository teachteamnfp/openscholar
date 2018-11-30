<?php

namespace Drupal\Tests\vsite\Unit;


use Drupal\Tests\UnitTestCase;
use Drupal\vsite\Plugin\VsitePathActivator;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @group vsite
 * @coversDefaultClass \Drupal\vsite\Plugin\VsitePathActivator
 *
 * Tests for the VsitePathActivator class
 */

class VsitePathActivatorTest extends UnitTestCase {

  /**
   * @var \Symfony\Component\DependencyInjection\ContainerBuilder
   */
  protected $container;

  /**
   * @var \Drupal\vsite\Plugin\VsitePathActivator;
   */
  protected $vsitePathActivator;

  /**
   * @var \PHPUnit_Framework_MockObject_MockObject
   */
  protected $vsiteContextManager;

  /**
   * @var \PHPUnit_Framework_MockObject_MockObject
   */
  protected $entityTypeManager;

  /**
   * @var \PHPUnit_Framework_MockObject_MockObject
   */
  protected $node;

  /**
   * @var \PHPUnit_Framework_MockObject_MockObject
   */
  protected $group;

  /**
   * Set up steps needed for the tests
   * Sets up a node-group relationship to be referred to later
   */
  public function setUp() {
    parent::setUp();

    $this->container = new ContainerBuilder();
    \Drupal::setContainer ($this->container);

    $this->vsiteContextManager = $this->createMock('\Drupal\vsite\Plugin\VsiteContextManager');
    // needed mocks:
    // entity type manager
    // group entity storage interface
    // group
    // group content storage interface
    // group content
    // node
    // current route match

    $this->entityTypeManager = $this->createMock ('\Drupal\Core\Entity\EntityTypeManagerInterface');

    $this->node = $this->createMock ('\Drupal\node\NodeInterface');
    $this->group = $this->createMock('\Drupal\group\Entity\GroupInterface');
    $group_content = $this->createMock ('\Drupal\group\Entity\GroupContentInterface');
    $group_content->method('getGroup')
      ->willReturn ($this->group);
    $group_content->method('getEntity')
      ->willReturn ($this->node);

    $groupStorage = $this->createMock('\Drupal\Core\Entity\EntityStorageInterface');
    $groupStorage->method ('load')
      ->with(1)
      ->willReturn($this->group);

    $groupContentStorage = $this->createMock ('\Drupal\group\Entity\Storage\GroupContentStorageInterface');
    $groupContentStorage->method('load')
      ->with(1)
      ->willReturn($group_content);
    $groupContentStorage->method('loadByEntity')
      ->with($this->node)
      ->willReturn ($group_content);

    $this->entityTypeManager->method('getStorage')
      ->will($this->returnCallback (function ($arg) use ($groupStorage, $groupContentStorage) {
        switch ($arg) {
          case 'group':
            return $groupStorage;
          case 'group_content':
            return $groupContentStorage;
          default:
            return null;
        }
      }));

    $this->vsitePathActivator = new VsitePathActivator($this->vsiteContextManager, $this->entityTypeManager);
  }

  /**
   * Test that a vsite is activated when a purl modifier is matched
   */
  public function testModifierMatched() {

    $currentRouteMatch = $this->createMock ('\Drupal\Core\Routing\CurrentRouteMatch');
    $currentRouteMatch->method('getParameter')
      ->with('node')
      ->willReturn ($this->node);
    $this->container->set('current_route_match', $currentRouteMatch);

    $event = $this->createMock ('\Drupal\purl\Event\ModifierMatchedEvent');
    $event->method('getValue')
      ->willReturn (1);

    $this->vsiteContextManager->expects($this->at(0))
      ->method('activateVsite')
      ->with($this->group);

    $this->vsitePathActivator->onModifierMatched ($event);

  }

  /**
   * Test that a vsite is activated when on the group entity path
   */
  public function testOnRequest() {

    $currentRouteMatch = $this->createMock ('\Drupal\Core\Routing\CurrentRouteMatch');
    $currentRouteMatch->method('getParameter')
      ->with('group')
      ->willReturn ($this->group);
    $this->container->set('current_route_match', $currentRouteMatch);

    $event = $this->createMock ('\Symfony\Component\HttpKernel\Event\GetResponseEvent');

    $this->vsiteContextManager->expects($this->at(0))
      ->method('activateVsite')
      ->with($this->group);

    $this->vsitePathActivator->onRequest ($event);
  }

}