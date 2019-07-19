<?php

namespace Drupal\Tests\cp_appearance\ExistingSite;

use Drupal\Core\Extension\Exception\UnknownExtensionException;
use Drupal\Core\Extension\ExtensionNameLengthException;

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
   * @covers ::uninstall
   */
  public function test(): void {
    /** @var \Drupal\Core\Config\ImmutableConfig $extension_config */
    $extension_config = $this->configFactory->get('core.extension');
    /** @var \Drupal\Core\State\StateInterface $state_store */
    $state_store = $this->container->get('state');
    /** @var \Drupal\cp_appearance\CustomThemeInstaller $custom_theme_installer */
    $custom_theme_installer = $this->container->get('cp_appearance.custom_theme_installer');

    // Test install.
    $custom_theme_installer->install([
      self::TEST_CUSTOM_THEME_3_NAME,
    ]);

    $theme_weight = $extension_config->get('theme.' . self::TEST_CUSTOM_THEME_3_NAME);
    $this->assertNotNull($theme_weight);

    $theme_data = $state_store->get('system.theme.data');
    $this->assertTrue(isset($theme_data[self::TEST_CUSTOM_THEME_3_NAME]));

    $block = $this->configFactory->get('block.block.' . self::TEST_CUSTOM_THEME_3_NAME . '_main_menu');
    $this->assertNotNull($block->get('id'));

    $this->assertTrue($this->themeHandler->themeExists(self::TEST_CUSTOM_THEME_3_NAME));

    // Test uninstall.
    $custom_theme_installer->uninstall([
      self::TEST_CUSTOM_THEME_3_NAME,
    ]);

    $theme_weight = $extension_config->get('theme.' . self::TEST_CUSTOM_THEME_3_NAME);
    $this->assertNull($theme_weight);

    $theme_data = $state_store->get('system.theme.data');
    $this->assertFalse(isset($theme_data[self::TEST_CUSTOM_THEME_3_NAME]));

    $this->assertFalse($this->themeHandler->themeExists(self::TEST_CUSTOM_THEME_3_NAME));
  }

  /**
   * Tests install exceptions.
   *
   * @covers ::install
   *
   * @throws \Drupal\Core\Extension\ExtensionNameLengthException
   */
  public function testInstallException(): void {
    /** @var \Drupal\cp_appearance\CustomThemeInstaller $custom_theme_installer */
    $custom_theme_installer = $this->container->get('cp_appearance.custom_theme_installer');

    $this->expectException(ExtensionNameLengthException::class);
    $custom_theme_installer->install([
      $this->randomMachineName(DRUPAL_EXTENSION_NAME_MAX_LENGTH + 1),
    ]);
  }

  /**
   * Tests uninstall exceptions.
   *
   * @covers ::uninstall
   *
   * @throws \Drupal\Core\Extension\ExtensionNameLengthException
   */
  public function testUninstallException(): void {
    // Setup.
    /** @var \Drupal\cp_appearance\CustomThemeInstaller $custom_theme_installer */
    $custom_theme_installer = $this->container->get('cp_appearance.custom_theme_installer');
    $theme_setting = $this->configFactory->getEditable('system.theme');
    $default_theme = $theme_setting->get('default');
    $default_admin_theme = $theme_setting->get('admin');
    $custom_theme_installer->install([
      self::TEST_CUSTOM_THEME_3_NAME,
    ]);

    // Test UnknownExtensionException.
    $this->expectException(UnknownExtensionException::class);
    $custom_theme_installer->uninstall([
      $this->randomMachineName(),
    ]);

    // Test InvalidArgumentException.
    $theme_setting->set('default', self::TEST_CUSTOM_THEME_3_NAME)->save();
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('The current default theme ' . self::TEST_CUSTOM_THEME_3_NAME . ' cannot be uninstalled.');
    $custom_theme_installer->uninstall([
      self::TEST_CUSTOM_THEME_3_NAME,
    ]);
    $theme_setting->set('default', $default_theme)->save();

    $theme_setting->set('admin', self::TEST_CUSTOM_THEME_3_NAME)->save();
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('The current administration theme ' . self::TEST_CUSTOM_THEME_3_NAME . ' cannot be uninstalled.');
    $custom_theme_installer->uninstall([
      self::TEST_CUSTOM_THEME_3_NAME,
    ]);
    $theme_setting->set('admin', $default_admin_theme)->save();

    // Cleanup.
    $custom_theme_installer->uninstall([
      self::TEST_CUSTOM_THEME_3_NAME,
    ]);
  }

}
