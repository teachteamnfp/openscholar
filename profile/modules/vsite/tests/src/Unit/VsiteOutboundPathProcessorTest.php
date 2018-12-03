<?php
/**
 * Created by PhpStorm.
 * User: New User
 * Date: 12/3/2018
 * Time: 2:27 PM
 */

namespace Drupal\Tests\vsite\Unit;


use Drupal\Tests\UnitTestCase;
use Drupal\vsite\Pathprocessor\VsiteOutboundPathProcessor;

class VsiteOutboundPathProcessorTest extends UnitTestCase {

  /**
   * @var VsiteOutboundPathProcessor
   * The object to test
   */
  protected $pathProcessor;

  /**
   * @var \PHPUnit_Framework_MockObject_MockObject
   * Mock for the ContextManager
   */
  protected $vsiteContextManager;

  public function setUp() {
    $this->vsiteContextManager = $this->createMock('\Drupal\vsite\Plugin\VsiteContextManager');

    $this->pathProcessor = new VsiteOutboundPathProcessor($this->vsiteContextManager);
  }

  public function testAdminPaths() {
    $options = [];
    $output_path = $this->pathProcessor->processOutbound ('admin', $options);
    $this->assertArrayHasKey ('purl_context', $options);
    $this->assertEquals (FALSE, $options['purl_context']);
    $this->assertEquals ('admin', $output_path);

    $options = [];
    $output_path = $this->pathProcessor->processOutbound ('admin/foo', $options);
    $this->assertArrayHasKey ('purl_context', $options);
    $this->assertEquals (FALSE, $options['purl_context']);
    $this->assertEquals ('admin/foo', $output_path);
  }

  public function testOutsideVsite() {
    $this->vsiteContextManager->method ('getActivePurl')
      ->willReturn (FALSE);

    $options = [];
    $output_path = $this->pathProcessor->processOutbound ('bar', $options);
    $this->assertArrayNotHasKey ('purl_exit', $options);
    $this->assertEquals ('bar', $output_path);

    $request = $this->createMock ('\Symfony\Component\HttpFoundation\Request');
    $request->method('get')
      ->willReturn(false);

    $options = [];
    $output_path = $this->pathProcessor->processOutbound ('bar', $options, $request);
    $this->assertEquals ('bar', $output_path);
  }

  public function testInVsite() {
    $this->vsiteContextManager->method('getActivePurl')
      ->willReturn ('foo');
    $this->vsiteContextManager->method('getAbsoluteUrl')
      ->willReturnCallback (function ($path) {
        return 'http://localhost/foo/'.$path;
      });

    $request = $this->createMock ('\Symfony\Component\HttpFoundation\Request');
    $request->method('get')
      ->willReturn(true);

    $options = [];
    $output_path = $this->pathProcessor->processOutbound ('foo/bar', $options);
    $this->assertArrayHasKey ('purl_exit', $options);
    $this->assertEquals (true, $options['purl_exit']);
    $this->assertEquals ('foo/bar', $output_path);

    $options = [];
    $output_path = $this->pathProcessor->processOutbound ('bar', $options, $request);
    $this->assertEquals ('http://localhost/foo/bar', $output_path, $request);

    $options = [
      'purl_exit' => true
    ];
    $output_path = $this->pathProcessor->processOutbound ('bar', $options, $request);
    $this->assertEquals ('bar', $output_path);

    $options = [
      'purl_context' => false,
    ];
    $output_path = $this->pathProcessor->processOutbound ('bar', $options, $request);
    $this->assertEquals ('bar', $output_path);
  }
}