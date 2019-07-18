<?php

namespace Drupal\Tests\cp_appearance\ExistingSite;

/**
 * Tests custom theme installation/uninstallation.
 *
 * @group kernel
 * @group cp-appearance
 * @coversDefaultClass \Drupal\cp_appearance\CustomThemeInstaller
 */
class CustomThemeInstallerTest extends TestBase {

  /**
   * @covers ::install
   */
  public function testInstall(): void {
    /** @var \Drupal\cp_appearance\CustomThemeInstaller $custom_theme_installer */
    $custom_theme_installer = $this->container->get('cp_appearance.custom_theme_installer');
    $custom_theme_installer->install([
      self::TEST_CUSTOM_THEME_3_NAME,
    ]);

    $this->assertTrue($this->themeHandler->themeExists(self::TEST_CUSTOM_THEME_3_NAME));
  }

}
