<?php

namespace Drupal\vsite\Cache;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\Context\CalculatedCacheContextInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Provides a cache context for an arbitrary vsite.
 *
 * Activate with 'group:{int}'.
 */
class VsiteCacheContext implements CalculatedCacheContextInterface {

  const VSITE_CACHE_CONTEXTS_NONE = 'vsite:none';

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructor.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getLabel() {
    return t('Active VSite');
  }

  /**
   * {@inheritdoc}
   */
  public function getContext($parameter = NULL) {
    if ($parameter) {
      if ($group = $this->entityTypeManager->getStorage('group')->load($parameter)) {
        return 'vsite:' . $group->id();
      }
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata($parameter = NULL) {
    return new CacheableMetadata();
  }

}
