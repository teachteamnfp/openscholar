<?php

namespace Drupal\Tests\vsite\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\vsite\Plugin\VsiteContextManager;
use Symfony\Component\DependencyInjection\ContainerBuilder;


/**
 * @group vsite
 * @coversDefaultClass VsiteContextManager
 */
class VsiteContextManagerTest extends UnitTestCase {


  /** @var ContainerBuilder */
  protected $container;

  /**
   * @var VsiteContextManager
   * The object to test
   */
  protected $vsiteContextManager;

  /** @var \PHPUnit_Framework_MockObject_MockObject */
  protected $eventDispatcher;


  public function setUp () {
    parent::setUp ();

    $this->container = new ContainerBuilder();

    $this->eventDispatcher = $this->createMock ('Symfony\Component\EventDispatcher\EventDispatcher');

    $this->vsiteContextManager = new VsiteContextManager($this->eventDispatcher);
  }

  public function testTestsRun() {
    $i = 5;

    $this->assertEquals (5, $i);
  }
}