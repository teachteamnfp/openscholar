<?php

namespace Drupal\os_theme_preview;

/**
 * Contract for the ThemePreview data type.
 */
interface ThemePreviewInterface {

  /**
   * Returns the name of the theme being previewed.
   *
   * @return string
   *   Machine name of the theme.
   */
  public function getName(): string;

  /**
   * Returns the id of the vsite where the preview was initiated.
   *
   * @return int
   *   The id.
   */
  public function getVsiteId(): int;

}
