<?php

namespace Drupal\Tests\os_theme_preview\ExistingSite;

use Drupal\os_theme_preview\Handler;
use Drupal\os_theme_preview\ThemePreview;
use Drupal\os_theme_preview\ThemePreviewException;

/**
 * HelperTest.
 *
 * @group kernel
 * @group os-theme-preview
 * @coversDefaultClass \Drupal\os_theme_preview\Handler
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
    $this->handler->startPreviewMode('hwpi_themeone_bentley', 47);
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
    $this->handler->startPreviewMode('hwpi_themeone_bentley', 0);

    $previewed_theme = $this->requestStack->getCurrentRequest()->getSession()->get(Handler::SESSION_KEY);
    $this->assertEquals(new ThemePreview('hwpi_themeone_bentley', 0), $previewed_theme);

    // When vsite is activated.
    $this->vsiteContextManager->activateVsite($group);
    $this->handler->startPreviewMode('hwpi_themeone_bentley', $group->id());

    $previewed_theme = $this->requestStack->getCurrentRequest()->getSession()->get(Handler::SESSION_KEY);
    $this->assertEquals(new ThemePreview('hwpi_themeone_bentley', (int) $group->id()), $previewed_theme);
  }

  /**
   * Test getPreviewedTheme.
   *
   * @covers ::getPreviewedThemeData
   */
  public function testGetPreviewedTheme(): void {
    // Negative test.
    $previewed_theme = $this->handler->getPreviewedThemeData();
    $this->assertNull($previewed_theme);

    // Positive test.
    $this->setSession($this->requestStack->getCurrentRequest());
    $this->requestStack->getCurrentRequest()->getSession()->set(Handler::SESSION_KEY, new ThemePreview('hwpi_themeone_bentley', 0));
    $previewed_theme = $this->handler->getPreviewedThemeData();
    $this->assertEquals(new ThemePreview('hwpi_themeone_bentley', 0), $previewed_theme);
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
    $this->handler->stopPreviewMode();
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

    $this->handler->startPreviewMode('hwpi_themeone_bentley', 0);
    $this->handler->stopPreviewMode();

    $previewed_theme = $this->requestStack->getCurrentRequest()->getSession()->get(Handler::SESSION_KEY);
    $this->assertNull($previewed_theme);
  }

}
