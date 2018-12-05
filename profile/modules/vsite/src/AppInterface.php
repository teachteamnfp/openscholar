<?php

namespace Drupal\vsite;

/**
 * App plugin interface.
 */
interface AppInterface {

  /**
   * Provide list of all content types this app controls.
   *
   * @return array
   *   List of Content Types
   */
  public function getGroupContentTypes();

}
