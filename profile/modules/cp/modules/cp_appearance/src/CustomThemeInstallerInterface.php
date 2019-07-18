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
   * It installs the theme, but it not does unnecessary cleanups, so that,
   * custom theme installed for a vsite should not affect performance in
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
   * @see \hook_themes_installed
   */
  public function install(array $theme_list) : bool;

  /**
   * Uninstalls a given list of themes.
   *
   * Uninstalling a theme removes all related configuration (like blocks) and
   * invokes the 'themes_uninstalled' hook.
   *
   * This is a light weight version of core's ThemeInstaller:uninstall.
   * It uninstalls the theme, but it not does unnecessary cleanups, so that,
   * custom theme uninstalled for a vsite should not affect performance in
   * other vsite.
   *
   * @param array $theme_list
   *   The themes to uninstall.
   *
   * @throws \Drupal\Core\Extension\Exception\UnknownExtensionException
   *   Thrown when trying to uninstall a theme that was not installed.
   *
   * @throws \InvalidArgumentException
   *   Thrown when trying to uninstall the default theme or the admin theme.
   *
   * @see \Drupal\Core\Extension\ThemeInstallerInterface::uninstall
   * @see \hook_themes_uninstalled
   */
  public function uninstall(array $theme_list) : void;

}
