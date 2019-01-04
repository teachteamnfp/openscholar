<?php

namespace Drupal\vsite_privacy\Plugin;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Cache\CacheBackendInterface;

/**
 * Class VsitePrivacyLevelManager.
 */
class VsitePrivacyLevelManager extends DefaultPluginManager implements VsitePrivacyLevelManagerInterface {

  /**
   * Creates a new VsitePrivacyLevelManager object.
   *
   * @param \Traversable $namespaces
   *   The namespaces.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module handler.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct(
      'Plugin/VsitePrivacyLevel',
      $namespaces,
      $module_handler,
      'Drupal\vsite_privacy\Plugin\VsitePrivacyLevelInterface',
      'Drupal\vsite_privacy\Annotation\VsitePrivacyLevel'
    );

    $this->alterInfo('vsite_privacy_level_info');
    $this->setCacheBackend($cache_backend, 'vsite_privacy_level_plugins');
  }

  /**
   * {@inheritdoc}
   */
  public function getOptions() {
    $defs = $this->getDefinitions();
    $options = [];

    uasort($defs, function ($a, $b) {
      if ($a['weight'] == $b['weight']) {
        return 0;
      }
      elseif ($a['weight'] > $b['weight']) {
        return 1;
      }
      else {
        return -1;
      }
    });

    foreach ($defs as $d) {
      /** @var \Drupal\Core\StringTranslation\TranslatableMarkup $title */
      $title = $d['title'];
      $options[$d['id']] = $title->render();
    }

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function getDescriptions() {
    $defs = $this->getDefinitions();
    $descs = [];

    foreach ($defs as $d) {
      /** @var \Drupal\Core\StringTranslation\TranslatableMarkup $desc */
      $desc = $d['description'];
      $descs[$d['id']] = $desc->render();
    }

    return $descs;

  }

  /**
   * {@inheritdoc}
   */
  public function checkAccessForPlugin(AccountInterface $account, string $plugin_id) {
    /** @var \Drupal\vsite_privacy\Plugin\VsitePrivacyLevelInterface $plugin */
    $plugin = $this->createInstance($plugin_id);
    return $plugin->checkAccess($account);
  }

}
