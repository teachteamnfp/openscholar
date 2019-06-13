<?php

namespace Drupal\Tests\os_theme_preview\ExistingSite;

use Drupal\os_theme_preview\Handler;
use Drupal\os_theme_preview\ThemePreview;
use Drupal\os_theme_preview\ThemePreviewException;
use Drupal\Tests\openscholar\ExistingSite\OsExistingSiteTestBase;
use Drupal\Tests\os_theme_preview\Traits\ThemePreviewTestTrait;

/**
 * Theme preview handler test.
 *
 * @group kernel
 * @group os-theme-preview
 * @coversDefaultClass \Drupal\os_theme_preview\Handler
 */
class HandlerOsThemePreviewTest extends OsExistingSiteTestBase {

  use ThemePreviewTestTrait;

  /**
   * Negative test for startPreviewMode.
   *
   * @covers ::startPreviewMode
   */
  public function testFalseStartPreviewMode(): void {
    $this->expectException(ThemePreviewException::class);
    $handler = $this->container->get('os_theme_preview.handler');
    $handler->startPreviewMode('hwpi_themeone_bentley', 47);
  }

  /**
   * Positive test for startPreviewMode.
   *
   * @covers ::startPreviewMode
   */
  public function testTrueStartPreviewMode(): void {
    $request_stack = $this->container->get('request_stack');
    $handler = $this->container->get('os_theme_preview.handler');
    $vsite_context_manager = $this->container->get('vsite.context_manager');

    $this->setSessionKernel($request_stack->getCurrentRequest());
    $group = $this->createGroup([
      'path' => [
        'alias' => '/start-preview',
      ],
    ]);

    // When vsite is not activated.
    $handler->startPreviewMode('hwpi_themeone_bentley', 0);

    $previewed_theme = $request_stack->getCurrentRequest()->getSession()->get(Handler::SESSION_KEY);
    $this->assertEquals(new ThemePreview('hwpi_themeone_bentley', 0), $previewed_theme);

    // When vsite is activated.
    $vsite_context_manager->activateVsite($group);
    $handler->startPreviewMode('hwpi_themeone_bentley', $group->id());

    $previewed_theme = $request_stack->getCurrentRequest()->getSession()->get(Handler::SESSION_KEY);
    $this->assertEquals(new ThemePreview('hwpi_themeone_bentley', (int) $group->id()), $previewed_theme);
  }

  /**
   * Test getPreviewedTheme.
   *
   * @covers ::getPreviewedThemeData
   */
  public function testGetPreviewedTheme(): void {
    $handler = $this->container->get('os_theme_preview.handler');
    $request_stack = $this->container->get('request_stack');

    // Negative test.
    $previewed_theme = $handler->getPreviewedThemeData();
    $this->assertNull($previewed_theme);

    // Positive test.
    $this->setSessionKernel($request_stack->getCurrentRequest());
    $request_stack->getCurrentRequest()->getSession()->set(Handler::SESSION_KEY, new ThemePreview('hwpi_themeone_bentley', 0));
    $previewed_theme = $handler->getPreviewedThemeData();
    $this->assertEquals(new ThemePreview('hwpi_themeone_bentley', 0), $previewed_theme);
  }

  /**
   * Negative test for stopPreviewMode.
   *
   * @covers ::stopPreviewMode
   */
  public function testFalseStopPreviewMode(): void {
    $handler = $this->container->get('os_theme_preview.handler');

    $this->expectException(ThemePreviewException::class);
    $handler->stopPreviewMode();
  }

  /**
   * Positive test for stopPreviewMode.
   *
   * @covers ::stopPreviewMode
   */
  public function testTrueStopPreviewMode(): void {
    $handler = $this->container->get('os_theme_preview.handler');
    $request_stack = $this->container->get('request_stack');

    $this->setSessionKernel($request_stack->getCurrentRequest());

    $handler->startPreviewMode('hwpi_themeone_bentley', 0);
    $handler->stopPreviewMode();

    $previewed_theme = $request_stack->getCurrentRequest()->getSession()->get(Handler::SESSION_KEY);
    $this->assertNull($previewed_theme);
  }

}
