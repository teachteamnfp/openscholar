<?php

namespace Drupal\vsite\Config;


use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigCollectionInfo;
use Drupal\Core\Config\ConfigCrudEvent;
use Drupal\Core\Config\ConfigFactoryOverrideBase;
use Drupal\Core\Config\ConfigFactoryOverrideInterface;
use Drupal\Core\Config\ConfigRenameEvent;
use Drupal\Core\Config\StorageInterface;

class VsiteConfigFactoryOverride extends ConfigFactoryOverrideBase implements ConfigFactoryOverrideInterface {

  /**
   * @return bool|StorageInterface
   */
  protected function getVsiteCollection() {
    /** @var \Drupal\group_purl\Context\GroupPurlContext $purl_context */
    $purl_context = \Drupal::service ('group_purl.context_provider');
    /** @var \Drupal\Core\Config\StorageInterface $config_storage */
    $config_storage = \Drupal::service('config.storage');
    if ($group = $purl_context->getGroupFromRoute()) {
      $purl = trim($group->path->getValue()[0]['alias'], '/');
      return $config_storage->createCollection('vsite.'.$purl);
    }
    return false;
  }
  /**
   * @inheritDoc
   */
  public function loadOverrides ($names) {
    if ($storage = $this->getVsiteCollection ()) {
      return $storage->readMultiple ($names);
    }
    return array();
  }

  /**
   * @inheritDoc
   *
   * Not sure what this is for
   */
  public function getCacheSuffix () {
    return 'vsite';
  }

  /**
   * @inheritDoc
   */
  public function createConfigObject ($name, $collection = StorageInterface::DEFAULT_COLLECTION) {
    return NULL;
  }

  /**
   * @inheritDoc
   */
  public function getCacheableMetadata ($name) {
    return new CacheableMetadata();
  }


  /**
   * @inheritDoc
   *
   * This function is called when Drupal wants to discover collections.
   * Unless suggested otherwise, I'm going to return one for every vsite
   */
  public function addCollections (ConfigCollectionInfo $collection_info) {
    $group_purl_provider = \Drupal::service('purl.plugin.provider_manager')->createPlugin('group_purl_provider');
    $modifiers = $group_purl_provider->getModifiers();
    foreach ($modifiers as $m) {
      $collection_info->addCollection ('vsite.'.(trim($m, '/')), $this);
    }
  }

  /**
   * @inheritDoc
   */
  public function onConfigSave (ConfigCrudEvent $event) {
    if ($storage = $this->getVsiteCollection ()) {
      $config = $event->getConfig ()->get();
      foreach ($config as $key => $value) {
        $storage->write($key, $value);
      }
      $event->stopPropagation ();
    }
  }

  /**
   * @inheritDoc
   */
  public function onConfigDelete (ConfigCrudEvent $event) {
    if ($storage = $this->getVsiteCollection ()) {
      $config = $event->getConfig ()->get();
      foreach ($config as $key => $value) {
        $storage->delete($key);
      }
      $event->stopPropagation ();
    }
  }

  /**
   * @inheritDoc
   */
  public function onConfigRename (ConfigRenameEvent $event) {
    if ($storage = $this->getVsiteCollection ()) {
      // when is this event fired?
    }
  }
}