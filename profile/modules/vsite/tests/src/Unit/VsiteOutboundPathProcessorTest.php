<?php

namespace Drupal\Tests\vsite\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\vsite\Pathprocessor\VsiteOutboundPathProcessor;

/**
 * Class VsiteOutboundPathProcessorTest.
 *
 * @package Drupal\Tests\vsite\Unit
 * @group unit
 * @group vsite
 * @group wip
 * @coversDefaultClass \Drupal\vsite\PathProcessor\VsiteOutboundPathProcessor
 */
class VsiteOutboundPathProcessorTest extends UnitTestCase {

  /**
   * The object to test.
   *
   * @var \Drupal\vsite\PathProcessor\VsiteOutboundPathProcessor
   */
  protected $pathProcessor;

  /**
   * Mock for the ContextManager.
   *
   * @var \PHPUnit_Framework_MockObject_MockObject
   */
  protected $vsiteContextManager;

  /**
   * Set up testing object and mocks.
   */
  public function setUp() {
    $this->vsiteContextManager = $this->createMock('\Drupal\vsite\Plugin\VsiteContextManager');

    $this->pathProcessor = new VsiteOutboundPathProcessor($this->vsiteContextManager);
  }

  /**
   * Test that admin paths don't get purls added.
   */
  public function testAdminPaths() {
    $options = [];
    $output_path = $this->pathProcessor->processOutbound('admin', $options);
    $this->assertArrayHasKey('purl_context', $options);
    $this->assertEquals(FALSE, $options['purl_context']);
    $this->assertEquals('admin', $output_path);

    $options = [];
    $output_path = $this->pathProcessor->processOutbound('admin/foo', $options);
    $this->assertArrayHasKey('purl_context', $options);
    $this->assertEquals(FALSE, $options['purl_context']);
    $this->assertEquals('admin/foo', $output_path);
  }

  /**
   * Test that urls outside of vsites don't get purls added.
   */
  public function testOutsideVsite() {
    $this->vsiteContextManager->method('getActivePurl')
      ->willReturn(FALSE);

    $options = [];
    $output_path = $this->pathProcessor->processOutbound('bar', $options);
    $this->assertArrayNotHasKey('purl_exit', $options);
    $this->assertEquals('bar', $output_path);

    $request = $this->createMock('\Symfony\Component\HttpFoundation\Request');
    $request->method('get')
      ->willReturn(FALSE);

    $options = [];
    $output_path = $this->pathProcessor->processOutbound('bar', $options, $request);
    $this->assertEquals('bar', $output_path);
  }

  /**
   * Test that urls in vsites are handled properly.
   */
  public function testInVsite() {
    $this->vsiteContextManager->method('getActivePurl')
      ->willReturn('foo');
    $this->vsiteContextManager->method('getAbsoluteUrl')
      ->willReturnCallback(function ($path) {
        return 'http://localhost/foo/' . $path;
      });

    $request = $this->createMock('\Symfony\Component\HttpFoundation\Request');
    $request->method('get')
      ->willReturn(TRUE);

    $options = [];
    $output_path = $this->pathProcessor->processOutbound('foo/bar', $options);
    $this->assertArrayHasKey('purl_exit', $options);
    $this->assertEquals(TRUE, $options['purl_exit']);
    $this->assertEquals('foo/bar', $output_path);

    $options = [];
    $output_path = $this->pathProcessor->processOutbound('bar', $options, $request);
    $this->assertEquals('http://localhost/foo/bar', $output_path, $request);

    $options = [
      'purl_exit' => TRUE,
    ];
    $output_path = $this->pathProcessor->processOutbound('bar', $options, $request);
    $this->assertEquals('bar', $output_path);

    $options = [
      'purl_context' => FALSE,
    ];
    $output_path = $this->pathProcessor->processOutbound('bar', $options, $request);
    $this->assertEquals('bar', $output_path);
  }

}
