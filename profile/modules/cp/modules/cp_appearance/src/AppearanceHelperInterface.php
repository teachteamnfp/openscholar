<?php

namespace Drupal\cp_appearance;

/**
 * Contract for AppearanceHelper.
 */
interface AppearanceHelperInterface {

  /**
   * Returns the themes for vsites.
   *
   * @return \Drupal\Core\Extension\Extension[]
   *   List of themes.
   */
  public function getThemes(): array;

}
