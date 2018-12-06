<?php

namespace Drupal\vsite\Plugin;

use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

class AppManager extends DefaultPluginManager implements AppManangerInterface {

  /**
   * Constructs an AppManager object.
   *
   * @param \Traversable $namespaces
   * @param \Drupal\Core\Cache\CacheBackendInterface
   * @param \Drupal\Core\Extension\ModuleHandlerInterface
   */
  public function __construct (\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct (
      'Plugin/App',
      $namespaces,
      $module_handler,
      'Drupal\vsite\AppInterface',
      'Drupal\vsite\Annotation\App'
    );

    $this->alterInfo ('app_info');
    $this->setCacheBackend($cache_backend, 'app_info_plugins');
  }

  public function getAppForBundle (string $bundle): string {
    $defs = $this->getDefinitions ();
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
}