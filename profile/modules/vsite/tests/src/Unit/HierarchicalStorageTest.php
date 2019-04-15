<?php

namespace Drupal\Tests\vsite\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\vsite\Config\HierarchicalStorage;

/**
 * Tests for the HierarchicalStorage object.
 *
 * This object allows StorageInterfaces to be stacked and prioritized.
 *
 * @group unit
 * @coversDefaultClass \Drupal\vsite\Config\HierarchicalStorage
 */
class HierarchicalStorageTest extends UnitTestCase {

  /**
   * The object to test.
   *
   * @var \Drupal\vsite\Config\HierarchicalStorage
   */
  protected $hierarchicalStorage;

  /**
   * The mock for global storage.
   *
   * @var \PHPUnit_Framework_MockObject_MockObject
   */
  protected $globalStorage;

  /**
   * The mock for the storage used to override global values.
   *
   * @var \PHPUnit_Framework_MockObject_MockObject
   */
  protected $overrideStorage;

  protected $globalVars = [
    'foo' => TRUE,
    'bar' => FALSE,
    'str' => 'hello world',
    'long.test' => 'def',
  ];

  protected $overrideVars = [
    'bar' => TRUE,
    'str' => 'just testing',
    'long.test' => 'abc',
    'long.override' => '123',
  ];

  /**
   * Setup test object, mocks, and behavior for global storage.
   */
  public function setUp() {
    parent::setUp();

    $this->globalStorage = $this->createMock('\Drupal\Core\Config\StorageInterface');
    $this->overrideStorage = $this->createMock('\Drupal\Core\Config\StorageInterface');

    $this->hierarchicalStorage = new HierarchicalStorage($this->globalStorage);
    $this->hierarchicalStorage->addStorage($this->overrideStorage, 0);

    $this->globalStorage->method('exists')
      ->willReturnCallback(function ($var) {
        return isset($this->globalVars[$var]);
      });

    $this->globalStorage->method('read')
      ->willReturnCallback(function ($var) {
        if (isset($this->globalVars[$var])) {
          return $this->globalVars[$var];
        }
        return NULL;
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

  /**
   * Testing the exist and various read methods.
   */
  public function testReading() {
    $this->overrideStorage->method('exists')
      ->willReturnCallback(function ($var) {
        return isset($this->overrideVars[$var]);
      });

    $this->overrideStorage->method('read')
      ->willReturnCallback(function ($var) {
        if (isset($this->overrideVars[$var])) {
          return $this->overrideVars[$var];
        }
        return NULL;
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

    $this->assertEquals(FALSE, $this->hierarchicalStorage->exists('nothing'));
    $this->assertEquals(TRUE, $this->hierarchicalStorage->exists('foo'));
    $this->assertEquals(TRUE, $this->hierarchicalStorage->exists('bar'));

    $this->assertEquals(TRUE, $this->hierarchicalStorage->read('foo'));
    $this->assertEquals(TRUE, $this->hierarchicalStorage->read('bar'));
    $this->assertEquals('just testing', $this->hierarchicalStorage->read('str'));

    $expect = [
      'foo' => TRUE,
      'bar' => TRUE,
      'str' => 'just testing',
    ];
    $this->assertArrayEquals($expect, $this->hierarchicalStorage->readMultiple([
      'foo',
      'bar',
      'str',
    ]));
  }

  /**
   * Test the write, delete, and rename methods.
   */
  public function testWriting() {
    $data = [
      'test' => 1,
    ];
    $this->overrideStorage->expects($this->once())
      ->method('write')
      ->with('foo', $data);

    $this->hierarchicalStorage->write('foo', $data);

    $deleted = $this->overrideVars;
    unset($deleted['bar']);

    $this->overrideStorage->method('read')
      ->willReturnCallback(function ($var) use ($deleted) {
        if (!empty($deleted[$var])) {
          return $deleted[$var];
        }
        return NULL;
      });

    $this->overrideStorage->expects($this->once())
      ->method('delete')
      ->with('bar');

    $this->hierarchicalStorage->delete('bar');

    $this->assertEquals(FALSE, $this->hierarchicalStorage->read('bar'));

    $this->overrideStorage->expects($this->once())
      ->method('rename')
      ->with('foo', 'foo2');

    $this->hierarchicalStorage->rename('foo', 'foo2');
    $this->assertEquals(FALSE, $this->hierarchicalStorage->read('foo2'));
  }

  /**
   * Test encoding methods.
   */
  public function testEncoding() {
    $this->globalStorage->expects($this->once())
      ->method('encode')
      ->with('value')
      ->willReturn('encoded');

    $this->assertEquals('encoded', $this->hierarchicalStorage->encode('value'));

    $this->globalStorage->expects($this->once())
      ->method('decode')
      ->with('encoded')
      ->willReturn('value');

    $this->assertEquals('value', $this->hierarchicalStorage->decode('encoded'));
  }

  /**
   * Test the methods that allow acting on a prefix.
   */
  public function testTheAllMethods() {
    $this->overrideStorage->expects($this->at(0))
      ->method('listAll')
      ->with('long')
      ->willReturn(['long.test', 'long.override']);

    $this->globalStorage->method('listAll')
      ->with('long')
      ->willReturn(['long.test']);

    $this->overrideStorage->expects($this->at(1))
      ->method('listAll')
      ->willReturn(['long.test', 'long.override']);

    $this->overrideStorage->expects($this->at(2))
      ->method('deleteAll')
      ->with('long');

    $this->overrideStorage->expects($this->at(3))
      ->method('listAll')
      ->with('long')
      ->willReturn(['long.test']);

    $expects = ['long.test', 'long.override'];
    $this->assertArrayEquals($expects, $this->hierarchicalStorage->listAll('long'));

    $this->assertArrayEquals(['long.test'], $this->hierarchicalStorage->listAllFromLevel('long'));
    $this->assertArrayEquals(['long.test', 'long.override'], $this->hierarchicalStorage->listAllFromLevel('long', 0));

    $this->hierarchicalStorage->deleteAll('long');

    $expects = ['long.test'];
    $this->assertArrayEquals($expects, $this->hierarchicalStorage->listAll('long'));
    $this->assertEquals('def', $this->hierarchicalStorage->read('long.test'));

    $this->assertArrayEquals([], $this->hierarchicalStorage->listAllFromLevel('long', -999));
  }

  /**
   * Test Collection handling.
   */
  public function testCollections() {
    $coll = $this->createMock('\Drupal\Core\Config\StorageInterface');
    $coll->method('getCollectionName')
      ->willReturn('collectionTest');
    $this->globalStorage->method('createCollection')
      ->willReturn($coll);

    $this->overrideStorage->expects($this->never())
      ->method('createCollection');

    $created = $this->hierarchicalStorage->createCollection('collectionTest');
    $this->assertEquals($coll, $created);
    $this->assertEquals('collectionTest', $created->getCollectionName());

    $this->overrideStorage->expects($this->once())
      ->method('getCollectionName')
      ->willReturn('vsite.site01');

    $this->assertEquals('vsite.site01', $this->hierarchicalStorage->getCollectionName());

    $this->globalStorage->expects($this->once())
      ->method('getAllCollectionNames')
      ->willReturn(['collectionTest']);

    $this->overrideStorage->expects($this->never())
      ->method('getAllCollectionNames');

    $expected = ['collectionTest'];
    $this->assertArrayEquals($expected, $this->hierarchicalStorage->getAllCollectionNames());

  }

}
