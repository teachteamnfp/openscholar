<?php

namespace Drupal\vsite\Plugin;

use Drupal\Component\Plugin\PluginManagerInterface;

/**
 * Interface for classes managing App plugin system.
 */
interface AppManangerInterface extends PluginManagerInterface {

  /**
   * Gets App for bundle.
   *
   * @param string $bundle
   *   Bundle name.
   *
   * @return string
   *   App name.
   */
  public function getAppForBundle(string $bundle) : string;

}
