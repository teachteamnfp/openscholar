<?php

namespace Drupal\cp_appearance;

/**
 * Contract for AppearanceSettingsBuilder.
 */
interface AppearanceSettingsBuilderInterface {

  /**
   * Returns the themes for vsites.
   *
   * @return \Drupal\Core\Extension\Extension[]
   *   List of themes.
   */
  public function getThemes(): array;

}
