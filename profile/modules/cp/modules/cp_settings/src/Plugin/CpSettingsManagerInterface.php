<?php

namespace Drupal\cp_settings\Plugin;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Component\Plugin\Discovery\CachedDiscoveryInterface;
use Drupal\Core\Cache\CacheableDependencyInterface;

interface CpSettingsManagerInterface extends PluginManagerInterface, CachedDiscoveryInterface, CacheableDependencyInterface {

  public function generateMenuLinks($base_plugin_definition);

  public function getForm($id);

  public function getPluginsForGroup(string $group);
}