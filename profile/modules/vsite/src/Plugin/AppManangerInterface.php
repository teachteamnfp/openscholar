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

  /**
   * Get group permissions for an app.
   *
   * @param string $app_id
   *   The app id.
   *
   * @return array
   *   The group permissions.
   */
  public function getViewContentGroupPermissionsForApp(string $app_id): array;

}
