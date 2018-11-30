<?php

namespace Drupal\Tests\vsite\Unit;

use Drupal\group\Entity\Group;
use Drupal\system\Tests\Routing\MockAliasManager;
use Drupal\Tests\UnitTestCase;
use Drupal\vsite\Event\VsiteActivatedEvent;
use Drupal\vsite\Plugin\VsiteContextManager;
use Drupal\vsite\VsiteEvents;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @group vsite
 * @coversDefaultClass \Drupal\vsite\Plugin\VsiteContextManager
 *
 * Tests for the VsiteContextManager class
 */
class VsiteContextManagerTest extends UnitTestCase {


  /**
   * @var \Symfony\Component\DependencyInjection\ContainerBuilder
   *
   * The dependency injection container
   */
  protected $container;

  /**
   * @var \Drupal\vsite\Plugin\VsiteContextManager
   * The object to test
   */
  protected $vsiteContextManager;

  /**
   * @var \PHPUnit_Framework_MockObject_MockObject
   *
   * Event Dispatcher mock object
   */
  protected $eventDispatcher;

  /**
   * Setup the tests
   */
  public function setUp() {
    parent::setUp();

    $this->container = new ContainerBuilder();
    \Drupal::setContainer ($this->container);

    $this->eventDispatcher = $this->createMock('Symfony\Component\EventDispatcher\EventDispatcher');

    $this->vsiteContextManager = new VsiteContextManager($this->eventDispatcher);

    $alias_manager = new MockAliasManager();
    $this->container->set('path.alias_manager', $alias_manager);
    $alias_manager->addAlias ('/group/1', '/site01');
  }


  /**
   * Test vsite activation and the getters that require an active vsite
   */
  public function testActivateVsite() {
    $group = $this->createMock('\Drupal\group\Entity\Group');

    $group->method('id')
      ->willReturn (1);

    $url = $this->createMock('\Drupal\Core\Url');
    $url->method('toString')
      ->willReturn('/site01');

    $group->method('toUrl')
      ->willReturn($url);

    $event = new VsiteActivatedEvent($group);

    $this->eventDispatcher->expects($this->at (0))
      ->method('dispatch')
      ->with(VsiteEvents::VSITE_ACTIVATED, $event);

    $this->vsiteContextManager->activateVsite($group);

    // that that roles activated as they should

    $this->assertEquals ($group, $this->vsiteContextManager->getActiveVsite ());
    $this->assertEquals('site01', $this->vsiteContextManager->getActivePurl ());
    $this->assertEquals('/site01/foo', $this->vsiteContextManager->getAbsoluteUrl ('foo'));
    // getStorage is currently not implemented. May be removed later
  }

}
