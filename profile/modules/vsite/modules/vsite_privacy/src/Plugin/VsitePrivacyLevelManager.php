<?php

namespace Drupal\vsite_privacy\Plugin;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\vsite_privacy\Plugin\VsitePrivacyLevelInterface;

class VsitePrivacyLevelManager extends DefaultPluginManager implements VsitePrivacyLevelManagerInterface {

  public function __construct (\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct (
      'Plugin/VsitePrivacyLevel',
      $namespaces,
      $module_handler,
      'Drupal\vsite_privacy\Plugin\VsitePrivacyLevelInterface',
      'Drupal\vsite_privacy\Annotation\VsitePrivacyLevel'
    );

    $this->alterInfo ('vsite_privacy_level_info');
    $this->setCacheBackend($cache_backend, 'vsite_privacy_level_plugins');
  }

  public function getOptions () {
    $defs = $this->getDefinitions ();
    $options = [];

    foreach ($defs as $d) {
      /** @var TranslatableMarkup $title */
      $title = $d['title'];
      $options[$d['id']] = $title->render();
    }

    return $options;
  }

  public function getDescriptions() {
    $defs = $this->getDefinitions ();
    $descs = [];

    foreach ($defs as $d) {
      /** @var TranslatableMarkup $desc */
      $desc = $d['description'];
      $descs[$d['id']] = $desc->render();
    }

    return $descs;

  }

  public function checkAccessForPlugin (AccountInterface $account, string $plugin_id) {
    /** @var VsitePrivacyLevelInterface $plugin */
    $plugin = $this->createInstance ($plugin_id);
    return $plugin->checkAccess ($account);
  }
}