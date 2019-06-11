<?php

namespace Drupal\cp_appearance\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for CustomTheme configuration entity.
 */
interface CustomThemeInterface extends ConfigEntityInterface {

  /**
   * Sets the base of the custom theme.
   *
   * @param string $theme
   *   The name of the theme.
   *
   * @return \Drupal\cp_appearance\Entity\CustomThemeInterface
   *   The custom theme this was called on.
   */
  public function setBaseTheme(string $theme): CustomThemeInterface;

  /**
   * Returns the base theme of the custom theme.
   *
   * @return string
   *   The theme name.
   */
  public function getBaseTheme(): string;

}
