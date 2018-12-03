<?php

namespace Drupal\Tests\vsite\Unit;


use Drupal\Tests\UnitTestCase;
use Drupal\vsite\Config\HierarchicalStorage;

class HierarchicalStorageTest extends UnitTestCase {

  /**
   * @var HierarchicalStorage
   * The object to test
   */
  protected $hierarchicalStorage;

  /**
   * @var \PHPUnit_Framework_MockObject_MockObject
   * The mock for global storage
   */
  protected $globalStorage;

  /**
   * @var \PHPUnit_Framework_MockObject_MockObject
   * The mock for the storage used to override global values
   */
  protected $overrideStorage;

  protected $globalVars = [
    'foo' => true,
    'bar' => false,
    'str' => 'hello world'
  ];

  protected $overrideVars = [
    'bar' => true,
    'str' => 'just testing'
  ];

  public function setUp () {
    parent::setUp ();

    $this->globalStorage = $this->createMock('\Drupal\Core\Config\StorageInterface');
    $this->overrideStorage = $this->createMock('\Drupal\Core\Config\StorageInterface');

    $this->hierarchicalStorage = new HierarchicalStorage($this->globalStorage);
    $this->hierarchicalStorage->addStorage($this->overrideStorage, 0);

    $this->globalStorage->method('exists')
      ->willReturnCallback(function ($var) {
        return !empty($this->globalVars[$var]);
      });

    $this->globalStorage->method('read')
      ->willReturnCallback(function ($var) {
        if (!empty($this->globalVars[$var])) {
          return $this->globalVars[$var];
        }
        return null;
      });

    $this->globalStorage->method('readMultiple')
      ->willReturnCallback(function (array $vars) {
        $vals = [];
        foreach ($this->globalVars as $g => $v) {
          if (in_array($g, $vars)) {
            $vals[$g] = $v;
          }
        }
        return $vals;
      });
  }

  public function testReading() {
    $this->overrideStorage->method('exist')
      ->willReturnCallback(function ($var) {
        return !empty($this->overrideVars);
      });

    $this->overrideStorage->method('read')
      ->willReturnCallback(function ($var) {
        if (!empty($this->overrideVars[$var])) {
          return $this->overrideVars[$var];
        }
        return null;
      });

    $this->overrideStorage->method('readMultiple')
      ->willReturnCallback(function (array $vars) {
        $vals = [];
        foreach ($this->overrideVars as $g => $v) {
          if (in_array($g, $vars)) {
            $vals[$g] = $v;
          }
        }
        return $vals;
      });

    $this->assertEquals(false, $this->hierarchicalStorage->exists('nothing'));
    $this->assertEquals(true, $this->hierarchicalStorage->exists('foo'));
    $this->assertEquals(true, $this->hierarchicalStorage->exists ('bar'));

    $this->assertEquals(true, $this->hierarchicalStorage->read('foo'));
    $this->assertEquals(true, $this->hierarchicalStorage->read('bar'));
    $this->assertEquals('just testing', $this->hierarchicalStorage->read('str'));

    $expect = [
      'foo' => true,
      'bar' => true,
      'str' => 'just testing'
    ];
    $this->assertArrayEquals($expect, $this->hierarchicalStorage->readMultiple(['foo', 'bar', 'str']));
  }

  public function testWriting() {
    $data = [
      'test' => 1
    ];
    $this->overrideStorage->expects($this->once())
      ->method('write')
      ->with('foo', $data);

    $this->hierarchicalStorage->write('foo', $data);

    $this->overrideStorage->expects($this->once ())
      ->method('delete')
      ->with('foo');

    $this->hierarchicalStorage->delete('foo');

    $this->overrideStorage->expects($this->once())
      ->method('rename')
      ->with('foo', 'foo2');

    $this->hierarchicalStorage->rename('foo', 'foo2');
  }

  public function testEncoding() {
    $this->overrideStorage->expects($this->once())
      ->method('encode')
      ->with('value')
      ->willReturn('encoded');

    $this->assertEquals('encoded', $this->hierarchicalStorage->encode('value'));

    $this->overrideStorage->expects($this->once())
      ->method('decode')
      ->with('encoded')
      ->willReturn('value');

    $this->assertEquals('value', $this->hierarchicalStorage->decode('encoded'));
  }

}