<?php

namespace Drupal\Tests\os_theme_preview\ExistingSite;

use Drupal\os_theme_preview\Handler;
use Drupal\os_theme_preview\ThemePreview;
use Drupal\Tests\openscholar\ExistingSite\OsExistingSiteTestBase;
use Drupal\Tests\os_theme_preview\Traits\ThemePreviewTestTrait;

/**
 * PreviewManagerTest.
 *
 * @group kernel
 * @group os-theme-preview
 * @coversDefaultClass \Drupal\os_theme_preview\PreviewManager
 */
class PreviewManagerOsThemePreviewTest extends OsExistingSiteTestBase {

  use ThemePreviewTestTrait;

  /**
   * @covers ::getActiveVsiteId
   */
  public function testGetActiveVsiteId(): void {
    $vsite_context_manager = $this->container->get('vsite.context_manager');
    $theme_preview_manager = $this->container->get('os_theme_preview.manager');

    $group = $this->createGroup();

    $this->assertSame(0, $theme_preview_manager->getActiveVsiteId());

    $vsite_context_manager->activateVsite($group);

    $this->assertSame((int) $group->id(), $theme_preview_manager->getActiveVsiteId());
  }

  /**
   * @covers ::isPreviewModeEnabled
   */
  public function testIsPreviewModeEnabled(): void {
    $theme_preview_manager = $this->container->get('os_theme_preview.manager');
    $request_stack = $this->container->get('request_stack');
    $vsite_context_manager = $this->container->get('vsite.context_manager');
    $group = $this->createGroup();

    // Non-vsite test.
    $this->assertFalse($theme_preview_manager->isPreviewModeEnabled());

    $this->setSessionKernel($request_stack->getCurrentRequest());
    $request_stack->getCurrentRequest()->getSession()->set(Handler::SESSION_KEY, new ThemePreview('hwpi_themeone_bentley', 0));

    $this->assertTrue($theme_preview_manager->isPreviewModeEnabled());

    // Vsite tests.
    $request_stack->getCurrentRequest()->getSession()->set(Handler::SESSION_KEY, new ThemePreview('hwpi_themeone_bentley', (int) $group->id()));

    $this->assertFalse($theme_preview_manager->isPreviewModeEnabled());

    $vsite_context_manager->activateVsite($group);

    $this->assertTrue($theme_preview_manager->isPreviewModeEnabled());
  }

}
