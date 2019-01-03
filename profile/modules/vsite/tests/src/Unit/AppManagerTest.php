<?php

namespace Drupal\Tests\vsite\Unit;

use Drupal\Component\Plugin\Discovery\DiscoveryInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\vsite\Plugin\AppManager;

/**
 * Class AppManagerTest.
 *
 * @package Drupal\Tests\vsite\Kernel\
 * @group unit
 * @covers \Drupal\vsite\Plugin\AppManager
 */
class AppManagerTest extends UnitTestCase {


  /**
   * The object we're testing.
   *
   * @var \Drupal\vsite\Plugin\AppManager
   */
  protected $appManager;

  /**
   * Cache Backend mock.
   *
   * @var \PHPUnit_Framework_MockObject_MockObject
   */
  protected $cache;

  /**
   * Module Handler mock.
   *
   * @var \PHPUnit_Framework_MockObject_MockObject
   */
  protected $moduleHandler;

  /**
   * Plugin definitions we will using in the tests.
   *
   * @var array
   */
  protected $pluginDefinitions = [
    'test' => [
      'id' => 'test',
      'class' => 'Drupal\vsite_module_test\Plugin\App\TestApp',
      'title' => 'Test',
      'canDisable' => TRUE,
      'entityType' => 'node',
      'bundle' => 'test',
    ],
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->cache = $this->createMock('\Drupal\Core\Cache\CacheBackendInterface');

    $this->moduleHandler = $this->createMock('\Drupal\Core\Extension\ModuleHandlerInterface');

    $this->appManager = new TestAppManager(new \ArrayObject(), $this->cache, $this->moduleHandler);

    $discovery = $this->createMock('\Drupal\Component\Plugin\Discovery\DiscoveryInterface');
    $discovery->method('getDefinitions')
      ->willReturn($this->pluginDefinitions);

    $this->appManager->setDiscovery($discovery);
  }

  /**
   * Test that we correctly return definitions from discovery.
   */
  public function testDefinitions() {
    $this->assertEquals(TRUE, $this->appManager->hasDefinition('test'));
    $definitions = $this->appManager->getDefinitions();
    $this->assertArrayHasKey('test', $definitions);
    $this->assertArrayHasKey('bundle', $definitions['test']);
    $this->assertEquals('test', $definitions['test']['bundle']);
  }

}

/**
 * Test class so we can override the discovery object.
 *
 * @package Drupal\Tests\vsite\Unit
 */
class TestAppManager extends AppManager {

  /**
   * Set discovery mock for the manager.
   */
  public function setDiscovery(DiscoveryInterface $discovery) {
    $this->discovery = $discovery;
  }

}
