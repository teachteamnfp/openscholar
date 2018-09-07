<?php
/**
 * Created by PhpStorm.
 * User: New User
 * Date: 9/7/2018
 * Time: 3:42 PM
 */

namespace Drupal\vsite\Config;


use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\Path\AliasManagerInterface;
use Drupal\group_purl\Context\GroupPurlContext;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class VsiteConfigFactory extends ConfigFactory implements ConfigFactoryInterface {

  protected $group_purl_context;

  protected $alias_manager;

  public function __construct(StorageInterface $storage, EventDispatcherInterface $event_dispatcher, TypedConfigManagerInterface $typed_config) {
    parent::__construct($storage, $event_dispatcher, $typed_config);
  }

  /**
   * @return bool|StorageInterface
   */
  protected function getVsiteCollection() {
    static $running = false;
    if (!$running) {
      $running = true;
      if (!isset($this->group_purl_context)) {
        $this->group_purl_context = \Drupal::service('group_purl.context_provider');
      }
      /** @var \Drupal\group\Entity\Group $group */
      if ($group = $this->group_purl_context->getGroupFromRoute()) {
        if (!isset ($this->alias_manager)) {
          $this->alias_manager = \Drupal::service ('path.alias_manager');
        }
        $path = $this->alias_manager->getAliasByPath('/group/' . $group->id ());
        $purl = substr($path, 1);

        return $this->storage->createCollection('vsite.'.$purl);
      }
      $running = false;
    }
    return false;
  }

  protected function createConfigObject($name, $immutable) {
    if ($immutable) {
      return parent::createConfigObject ($name, $immutable);
    }
    $collection = $this->getVsiteCollection ();
    return new Config($name, $collection ? $collection : $this->storage, $this->eventDispatcher, $this->typedConfigManager);
  }
}