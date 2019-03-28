<?php

namespace Drupal\os_theme_preview;

/**
 * The contract for theme preview helper.
 *
 * Most probably this is going to be renamed to something better, once the scope
 * becomes more clear.
 */
interface HelperInterface {

  /**
   * Starts the preview mode.
   *
   * @param string $theme
   *   The name of the theme to preview.
   *
   * @throws \Drupal\os_theme_preview\ThemePreviewException
   */
  public function startPreviewMode($theme);

  /**
   * Returns the theme name currently being previewed.
   *
   * @return string|null
   *   The theme name being previewed, otherwise NULL.
   */
  public function getPreviewedTheme(): ?string;

}
