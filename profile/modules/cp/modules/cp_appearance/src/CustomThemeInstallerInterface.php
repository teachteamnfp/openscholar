<?php

namespace Drupal\cp_appearance;

/**
 * Manages custom theme installation/uninstallation.
 */
interface CustomThemeInstallerInterface {

  /**
   * Installs a given list of themes.
   *
   * This is a light weight version of core's ThemeInstaller:install.
   * It installs the theme, but does not invalidates unnecessary cache tags, so
   * that, custom theme installed for a vsite should not affect performance in
   * other vsite.
   *
   * @param array $theme_list
   *   An array of theme names.
   *
   * @return bool
   *   Whether any of the given themes have been installed.
   *
   * @throws \Drupal\Core\Extension\ExtensionNameLengthException
   *   Thrown when the theme name is to long.
   *
   * @throws \Drupal\Core\Extension\Exception\UnknownExtensionException
   *   Thrown when the theme does not exist.
   *
   * @see \Drupal\Core\Extension\ThemeInstallerInterface::install
   */
  public function install(array $theme_list) : bool;

}
