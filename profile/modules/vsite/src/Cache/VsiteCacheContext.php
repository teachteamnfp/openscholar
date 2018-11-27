<?php

namespace Drupal\vsite\Cache;


use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\Context\CalculatedCacheContextInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

class VsiteCacheContext implements CalculatedCacheContextInterface {

  /** @var EntityTypeManagerInterface */
  protected $entityTypeManager;

  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * @inheritDoc
   */
  public static function getLabel () {
    return t('Active VSite');
  }

  /**
   * @inheritDoc
   */
  public function getContext ($parameter = NULL) {
    if ($parameter) {
      if ($group = $this->entityTypeManager->getStorage ('group')->load ($parameter)) {
        return 'group:'.$group->id ();
      }
    }
    return null;
  }

  /**
   * @inheritDoc
   */
  public function getCacheableMetadata ($parameter = NULL) {
    return new CacheableMetadata();
  }
}