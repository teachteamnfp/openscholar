<?php

namespace Drupal\vsite\Plugin;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Manager for the App plugin system.
 */
class AppManager extends DefaultPluginManager implements AppManangerInterface {

  /**
   * Constructs an AppManager object.
   *
   * @param \Traversable $namespaces
   *   Namespace object.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module handler.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct(
      'Plugin/App',
      $namespaces,
      $module_handler,
      'Drupal\vsite\AppInterface',
      'Drupal\vsite\Annotation\App'
    );

    $this->alterInfo('app_info');
    $this->setCacheBackend($cache_backend, 'app_info_plugins');
  }

  /**
   * {@inheritdoc}
   */
  public function getAppForBundle(string $bundle): string {
    $defs = $this->getDefinitions();
    $app = '';
    foreach ($defs as $d) {
      if ($d['bundle'] == $bundle) {
        $app = $d['id'];
      }
    }

    if ($app) {
      return $app;
    }

    return '';
  }

  protected function findDefinitions() {
    $definitions = $this->getDiscovery()->getDefinitions();
    foreach ($definitions as $plugin_id => &$definition) {
      $this->processDefinition($definition, $plugin_id);
    }
    $this->alterDefinitions($definitions);
    // If this plugin was provided by a module that does not exist, remove the
    // plugin definition.
    foreach ($definitions as $plugin_id => $plugin_definition) {
      $provider = $this->extractProviderFromDefinition($plugin_definition);
      if ($provider && !in_array($provider, ['core', 'component']) && !$this->providerExists($provider)) {
        unset($definitions[$plugin_id]);
      }
    }
    return $definitions;
  }

}
