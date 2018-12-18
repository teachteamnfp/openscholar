<?php

namespace Drupal\cp_settings\Plugin;


use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

class CpSettingsManager extends DefaultPluginManager implements CpSettingsManagerInterface {


  public function __construct (\Traversable $namespaces, CacheBackendInterface $cacheBackend, ModuleHandlerInterface $module_handler) {
    parent::__construct (
      'Plugin/CpSetting',
      $namespaces,
      $module_handler,
      'Drupal\cp_settings\CpSettingInterface',
      'Drupal\cp_settings\Annotation\CpSetting'
    );

    $this->alterInfo ('cp_settings');
    $this->setCacheBackend ($cacheBackend, 'cp_settings_plugins');
  }

  public function generateMenuLinks($base_plugin_definition) {
    $links = [];
    $defs = $this->getDefinitions ();
    foreach ($defs as $d) {
      $links[$d['group']['id']] = [
        'title' => $d['group']['title']->render(),
        'route_name' => 'cp.settings.group',
        'route_parameters' => ['setting_group' => $d['group']['id']],
        'parent' => $d['group']['parent']
      ] + $base_plugin_definition;
    }
    return $links;
  }

  public function getForm ($id) {
    // TODO: Implement getForm() method.
  }

  public function getPluginsForGroup (string $group) {
    $defs = $this->getDefinitions ();
    $defs = array_filter($defs, function ($a) use ($group) {
      return ($a['group']['id'] == $group);
    });

    $plugins = array_map(function ($a) {
      return $this->createInstance ($a['id']);
    }, $defs);

    return $plugins;
  }
}