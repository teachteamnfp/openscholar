<?php

namespace Drupal\Tests\os_theme_preview\ExistingSite;

use Drupal\os_theme_preview\Helper;
use Drupal\os_theme_preview\ThemePreviewException;

/**
 * HelperTest.
 *
 * @group kernel
 * @group other
 * @coversDefaultClass \Drupal\os_theme_preview\Helper
 */
class HelperTest extends TestBase {

  /**
   * Negative test for startPreviewMode.
   *
   * @covers ::startPreviewMode
   *
   * @throws \Drupal\os_theme_preview\ThemePreviewException
   */
  public function testFalseStartPreviewMode() {
    $this->expectException(ThemePreviewException::class);
    $this->helper->startPreviewMode('hwpi_themeone_bentley');
  }

  /**
   * Positive test for startPreviewMode.
   *
   * @covers ::startPreviewMode
   *
   * @throws \Drupal\os_theme_preview\ThemePreviewException
   */
  public function testTrueStartPreviewMode() {
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
  public function testGetPreviewedTheme() {
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
   *
   * @throws \Drupal\os_theme_preview\ThemePreviewException
   */
  public function testFalseStopPreviewMode() {
    $this->expectException(ThemePreviewException::class);
    $this->helper->stopPreviewMode();
  }

  /**
   * Positive test for stopPreviewMode.
   *
   * @covers ::stopPreviewMode
   *
   * @throws \Drupal\os_theme_preview\ThemePreviewException
   */
  public function testTrueStopPreviewMode() {
    $this->setSession($this->requestStack->getCurrentRequest());

    $this->helper->startPreviewMode('hwpi_themeone_bentley');
    $this->helper->stopPreviewMode();

    $previewed_theme = $this->requestStack->getCurrentRequest()->getSession()->get(Helper::SESSION_KEY);
    $this->assertNull($previewed_theme);
  }

}
