<?php

namespace Drupal\os_theme_preview;

/**
 * Contract for vsite wrapper.
 *
 * Could be renamed to something better once the scope becomes clearer.
 */
interface VsiteWrapperInterface {

  /**
   * Returns the id of active vsite.
   *
   * @return int
   *   Id of active vsite. If there is no active vsite, then 0.
   *
   * @see \Drupal\vsite\Plugin\VsiteContextManagerInterface::getActiveVsite
   */
  public function getActiveVsiteId(): int;

}
