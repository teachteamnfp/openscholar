<?php

namespace Drupal\Tests\os_theme_preview\Unit;

use Drupal\os_theme_preview\ThemePreview;
use Drupal\Tests\UnitTestCase;

/**
 * ThemePreviewTest.
 *
 * @group unit
 */
class ThemePreviewTest extends UnitTestCase {

  /**
   * Test.
   */
  public function test(): void {
    $theme_preview = new ThemePreview('test', '/vsite1');
    $this->assertSame('test', $theme_preview->getName());
    $this->assertSame('/vsite1', $theme_preview->getBasePath());
  }

}
