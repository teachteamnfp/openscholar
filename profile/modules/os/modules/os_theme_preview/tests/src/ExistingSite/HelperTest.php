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
   * Negative test for startPreviewMode.
   *
   * @covers ::startPreviewMode
   *
   * @throws \Drupal\os_theme_preview\ThemePreviewException
   */
  public function testFalseStartPreviewMode(): void {
    $this->expectException(ThemePreviewException::class);
    $this->helper->startPreviewMode('hwpi_themeone_bentley');
  }

  /**
   * Positive test for startPreviewMode.
   *
   * @covers ::startPreviewMode
   *
   * @throws \Drupal\os_theme_preview\ThemePreviewException
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function testTrueStartPreviewMode(): void {
    $this->setSession($this->requestStack->getCurrentRequest());
    $group = $this->createGroup([
      'path' => [
        'alias' => '/start-preview',
      ],
    ]);

    // When vsite is not activated.
    $this->helper->startPreviewMode('hwpi_themeone_bentley');

    $previewed_theme = $this->requestStack->getCurrentRequest()->getSession()->get(Helper::SESSION_KEY);
    $this->assertSame([
      'name' => 'hwpi_themeone_bentley',
      'path' => '/',
    ], $previewed_theme);

    // When vsite is activated.
    $this->vsiteContextManager->activateVsite($group);
    $this->helper->startPreviewMode('hwpi_themeone_bentley');

    $previewed_theme = $this->requestStack->getCurrentRequest()->getSession()->get(Helper::SESSION_KEY);
    $this->assertSame([
      'name' => 'hwpi_themeone_bentley',
      'path' => '/start-preview/',
    ], $previewed_theme);
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
    $this->requestStack->getCurrentRequest()->getSession()->set(Helper::SESSION_KEY, [
      'name' => 'hwpi_themeone_bentley',
      'path' => '/',
    ]);
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
  public function testFalseStopPreviewMode(): void {
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
  public function testTrueStopPreviewMode(): void {
    $this->setSession($this->requestStack->getCurrentRequest());

    $this->helper->startPreviewMode('hwpi_themeone_bentley');
    $this->helper->stopPreviewMode();

    $previewed_theme = $this->requestStack->getCurrentRequest()->getSession()->get(Helper::SESSION_KEY);
    $this->assertNull($previewed_theme);
  }

}
