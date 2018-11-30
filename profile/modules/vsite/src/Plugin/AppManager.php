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
   *   namespace object.
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

}
