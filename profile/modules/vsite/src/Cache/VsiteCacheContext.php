<?php

namespace Drupal\vsite\Cache;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\Context\CalculatedCacheContextInterface;
use Drupal\vsite\Plugin\VsiteContextManagerInterface;

/**
 * Provides a cache context for an arbitrary vsite.
 *
 * Activate with 'vsite:{int}'.
 */
class VsiteCacheContext implements CalculatedCacheContextInterface {

  const VSITE_CACHE_CONTEXTS_NONE = 'vsite:none';

  /**
   * Vsite Context Manager.
   *
   * @var \Drupal\vsite\Plugin\VsiteContextManagerInterface
   */
  protected $vsiteContextManager;

  /**
   * Constructor.
   */
  public function __construct(VsiteContextManagerInterface $vsiteContextManager) {
    $this->vsiteContextManager = $vsiteContextManager;
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
    if ($group = $this->vsiteContextManager->getActiveVsite()) {
      return $group->id();
    }
    else {
      return 'none';
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata($parameter = NULL) {
    return new CacheableMetadata();
  }

}
