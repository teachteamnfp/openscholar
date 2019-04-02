<?php

namespace Drupal\Tests\os_theme_preview\ExistingSite;

use Drupal\os_theme_preview\Helper;
use Drupal\os_theme_preview\ThemePreviewException;

/**
 * HelperTest.
 *
 * @group kernel
 * @group os-theme-preview
 * @coversDefaultClass \Drupal\os_theme_preview\Helper
 */
class HelperTest extends TestBase {

  /**
   * Positive test for startPreviewMode.
   *
   * @covers ::startPreviewMode
   */
  public function testTrueStartPreviewMode(): void {
    $this->setSession($this->requestStack->getCurrentRequest());

    $this->helper->startPreviewMode('hwpi_themeone_bentley');

    $previewed_theme = $this->requestStack->getCurrentRequest()->getSession()->get(Helper::SESSION_KEY);
    $this->assertEquals('hwpi_themeone_bentley', $previewed_theme);
  }

  /**
   * Test getPreviewedTheme.
   *
   * @covers ::getPreviewedTheme
   */
  public function testGetPreviewedTheme(): void {
    // Negative test.
    $previewed_theme = $this->helper->getPreviewedTheme();
    $this->assertNull($previewed_theme);

    // Positive test.
    $this->setSession($this->requestStack->getCurrentRequest());
    $this->requestStack->getCurrentRequest()->getSession()->set(Helper::SESSION_KEY, 'hwpi_themeone_bentley');
    $previewed_theme = $this->helper->getPreviewedTheme();
    $this->assertEquals('hwpi_themeone_bentley', $previewed_theme);
  }

  /**
   * Negative test for stopPreviewMode.
   *
   * @covers ::stopPreviewMode
   */
  public function testFalseStopPreviewMode(): void {
    $this->expectException(ThemePreviewException::class);
    $this->helper->stopPreviewMode();
  }

  /**
   * Positive test for stopPreviewMode.
   *
   * @covers ::stopPreviewMode
   */
  public function testTrueStopPreviewMode(): void {
    $this->setSession($this->requestStack->getCurrentRequest());

    $this->helper->startPreviewMode('hwpi_themeone_bentley');
    $this->helper->stopPreviewMode();

    $previewed_theme = $this->requestStack->getCurrentRequest()->getSession()->get(Helper::SESSION_KEY);
    $this->assertNull($previewed_theme);
  }

}
