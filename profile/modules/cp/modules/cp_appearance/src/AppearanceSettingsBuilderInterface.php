<?php

namespace Drupal\cp_appearance;

use Drupal\Core\Extension\Extension;

/**
 * Contract for AppearanceSettingsBuilder.
 */
interface AppearanceSettingsBuilderInterface {

  /**
   * Returns the featured themes for vsites.
   *
   * @return \Drupal\Core\Extension\Extension[]
   *   List of themes.
   */
  public function getFeaturedThemes(): array;

  /**
   * Determines if a theme is set as default.
   *
   * It handles the case when a flavor is set as default.
   *
   * @param \Drupal\Core\Extension\Extension $theme
   *   The theme.
   *
   * @return bool
   *   TRUE if set as default. Otherwise FALSE.
   */
  public function themeIsDefault(Extension $theme): bool;

  /**
   * Returns the custom themes.
   *
   * @return \Drupal\Core\Extension\Extension[]
   *   List of custom themes.
   */
  public function getCustomThemes(): array;

}
