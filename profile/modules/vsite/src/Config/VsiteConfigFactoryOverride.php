<?php

namespace Drupal\vsite\Config;


use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigCollectionInfo;
use Drupal\Core\Config\ConfigCrudEvent;
use Drupal\Core\Config\ConfigFactoryOverrideBase;
use Drupal\Core\Config\ConfigFactoryOverrideInterface;
use Drupal\Core\Config\ConfigRenameEvent;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Url;

class VsiteConfigFactoryOverride extends ConfigFactoryOverrideBase implements ConfigFactoryOverrideInterface {

  private static $blacklist = [
    'language.entity.en',
    'language.entity.und',
    'language.entity.zxx'
  ];

  /**
   * @return bool|StorageInterface
   */
  protected function getVsiteCollection() {
    static $running = false;
    /** @var \Drupal\group_purl\Context\GroupPurlContext $purl_context */
    $purl_context = \Drupal::service ('group_purl.context_provider');
    /** @var \Drupal\Core\Config\StorageInterface $config_storage */
    $config_storage = \Drupal::service('config.storage');
    if (!$running) {
      $running = true;
      /** @var \Drupal\group\Entity\Group $group */
      if ($group = $purl_context->getGroupFromRoute()) {
        /** @var \Drupal\Core\Path\AliasManagerInterface $alias_manager */
        $alias_manager = \Drupal::service('path.alias_manager');
        $path = $alias_manager->getAliasByPath('/group/' . $group->id ());
        $purl = substr($path, 1);

        return $config_storage->createCollection('vsite.'.$purl);
      }
      $running = false;
    }
    return false;
  }
  /**
   * @inheritDoc
   */
  public function loadOverrides ($names) {
    static $searched = array();
    $names = array_diff($names, static::$blacklist);
    if (!empty($names) && $storage = $this->getVsiteCollection ()) {
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
    /** @var \Drupal\purl\Plugin\ProviderManager $manager */
    if ($manager = \Drupal::service('purl.plugin.provider_manager')) {
      /** @var \Drupal\purl\Plugin\Purl\Provider\ProviderInterface $group_purl_provider */
      $group_purl_provider = $manager->getProvider ('group_purl_provider');
      $modifiers = $group_purl_provider->getModifierData ();
      foreach ($modifiers as $m) {
        $collection_info->addCollection ('vsite.' . (trim ($m, '/')), $this);
      }
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