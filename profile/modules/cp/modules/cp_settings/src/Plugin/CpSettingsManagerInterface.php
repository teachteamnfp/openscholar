<?php

namespace Drupal\cp_settings\Plugin;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Component\Plugin\Discovery\CachedDiscoveryInterface;
use Drupal\Core\Cache\CacheableDependencyInterface;

/**
 * Interface CpSettingsManagerInterface.
 */
interface CpSettingsManagerInterface extends PluginManagerInterface, CachedDiscoveryInterface, CacheableDependencyInterface {

  /**
   * Generates menu links.
   *
   * @param array $base_plugin_definition
   *   Base plugin definition.
   */
  public function generateMenuLinks(array $base_plugin_definition);

  /**
   * Returns form.
   *
   * @param string $id
   *   Form id.
   */
  public function getForm($id);

  /**
   * Returns plugins for group.
   *
   * @param string $group
   *   Group name.
   */
  public function getPluginsForGroup(string $group);

}
