<?php

namespace Drupal\os_theme_preview;

/**
 * Contract for theme preview manager.
 */
interface PreviewManagerInterface {

  /**
   * Returns the id of active vsite.
   *
   * @return int
   *   Id of active vsite. If there is no active vsite, then 0.
   *
   * @see \Drupal\vsite\Plugin\VsiteContextManagerInterface::getActiveVsite
   */
  public function getActiveVsiteId(): int;

  /**
   * Determines if preview mode is enabled.
   *
   * @return bool
   *   TRUE if enabled, otherwise FALSE.
   *
   * @see \Drupal\os_theme_preview\Handler::getPreviewedThemeData
   */
  public function isPreviewModeEnabled(): bool;

}
