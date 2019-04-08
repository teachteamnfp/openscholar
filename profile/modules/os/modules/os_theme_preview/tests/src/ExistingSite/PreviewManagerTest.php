<?php

namespace Drupal\Tests\os_theme_preview\ExistingSite;

use Drupal\os_theme_preview\Handler;
use Drupal\os_theme_preview\ThemePreview;

/**
 * PreviewManagerTest.
 *
 * @group kernel
 * @group os-theme-preview
 * @coversDefaultClass \Drupal\os_theme_preview\PreviewManager
 */
class PreviewManagerTest extends TestBase {

  /**
   * @covers ::getActiveVsiteId
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testGetActiveVsiteId(): void {
    $group = $this->createGroup();

    $this->assertSame(0, $this->themePreviewManager->getActiveVsiteId());

    $this->vsiteContextManager->activateVsite($group);

    $this->assertSame((int) $group->id(), $this->themePreviewManager->getActiveVsiteId());
  }

  /**
   * @covers ::isPreviewModeEnabled
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testIsPreviewModeEnabled(): void {
    $group = $this->createGroup();

    // Non-vsite test.
    $this->assertFalse($this->themePreviewManager->isPreviewModeEnabled());

    $this->setSession($this->requestStack->getCurrentRequest());
    $this->requestStack->getCurrentRequest()->getSession()->set(Handler::SESSION_KEY, new ThemePreview('hwpi_themeone_bentley', 0));

    $this->assertTrue($this->themePreviewManager->isPreviewModeEnabled());

    // Vsite tests.
    $this->requestStack->getCurrentRequest()->getSession()->set(Handler::SESSION_KEY, new ThemePreview('hwpi_themeone_bentley', (int) $group->id()));

    $this->assertFalse($this->themePreviewManager->isPreviewModeEnabled());

    $this->vsiteContextManager->activateVsite($group);

    $this->assertTrue($this->themePreviewManager->isPreviewModeEnabled());
  }

}
