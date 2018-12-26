<?php

namespace Drupal\vsite\Plugin;

use Drupal\Component\Plugin\PluginManagerInterface;

/**
 * Interface for classes managing App plugin system.
 */
interface AppManangerInterface extends PluginManagerInterface {

  public function getAppForBundle(string $bundle) : string;

}
