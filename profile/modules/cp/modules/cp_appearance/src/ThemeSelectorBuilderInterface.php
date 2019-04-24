<?php

namespace Drupal\cp_appearance;

use Drupal\Core\Extension\Extension;

/**
 * Contract for ThemeSelectorBuilder service.
 */
interface ThemeSelectorBuilderInterface {

  /**
   * Returns the screenshot uri for a theme.
   *
   * At first the theme is checked for screenshot, if not found, then it's base
   * theme would be checked for screenshot.
   *
   * @param \Drupal\Core\Extension\Extension $theme
   *   The theme.
   *
   * @return string|null
   *   The screenshot path if exists, otherwise NULL.
   */
  public function getScreenshotUri(Extension $theme): ?string;

}
