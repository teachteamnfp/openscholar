<?php

namespace Drupal\Tests\vsite\Unit;

use Drupal\group\Entity\Group;
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

    $this->eventDispatcher = $this->createMock('Symfony\Component\EventDispatcher\EventDispatcher');

    $this->vsiteContextManager = new VsiteContextManager($this->eventDispatcher);
  }

  /**
   * Simple test to make sure the infrastructure works. Replace with real tests later
   */
  public function testTestsRun() {
    $i = 5;

    $this->assertEquals(5, $i);
  }

  public function testActivateVsite() {
    $group = $this->createMock('\Drupal\group\Entity\Group');

    $event = new VsiteActivatedEvent($group);

    $this->eventDispatcher->expects($this->at (0))
      ->method('dispatch')
      ->with(VsiteEvents::VSITE_ACTIVATED, $event);

    $this->vsiteContextManager->activateVsite($group);
  }

}
