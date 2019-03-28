<?php

namespace Drupal\Tests\os_theme_preview\ExistingSite;

use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * HelperTest.
 *
 * @group kernel
 * @group other
 * @coversDefaultClass \Drupal\os_theme_preview\Helper
 */
class HelperTest extends ExistingSiteBase {

  /**
   * Helper Service.
   *
   * @var \Drupal\os_theme_preview\HelperInterface
   */
  protected $helper;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->helper = $this->container->get('os_theme_preview.helper');
  }

  /**
   * Negative test for startPreviewMode.
   *
   * @covers ::startPreviewMode
   *
   * @throws \Drupal\os_theme_preview\ThemePreviewException
   */
  public function testStartPreviewMode() {
    $this->expectException('\Drupal\os_theme_preview\ThemePreviewException');
    $this->helper->startPreviewMode('hwpi_themeone_bentley');
  }

  /**
   * Negative test for getPreviewedTheme.
   *
   * @covers ::getPreviewedTheme
   */
  public function testGetPreviewedTheme() {
    $previewed_theme = $this->helper->getPreviewedTheme();
    $this->assertNull($previewed_theme);
  }

  /**
   * Negative test for stopPreviewMode.
   *
   * @covers ::stopPreviewMode
   *
   * @throws \Drupal\os_theme_preview\ThemePreviewException
   */
  public function testStopPreviewMode() {
    $this->expectException('\Drupal\os_theme_preview\ThemePreviewException');
    $this->helper->stopPreviewMode();
  }

}
