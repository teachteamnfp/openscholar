<?php

namespace Drupal\os_app_access\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\os_app_access\AppAccessLevels;
use Drupal\vsite\Plugin\AppManangerInterface;
use Drupal\vsite\Plugin\VsiteContextManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AppAccess.
 */
class AppAccess implements AccessInterface, ContainerInjectionInterface {

  /**
   * Config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Vsite context manager.
   *
   * @var \Drupal\vsite\Plugin\VsiteContextManagerInterface
   */
  protected $vsiteContextManager;

  /**
   * App manager.
   *
   * @var \Drupal\vsite\Plugin\AppManangerInterface
   */
  protected $appManager;

  /**
   * Creates a new AppAccess object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory service.
   * @param \Drupal\vsite\Plugin\VsiteContextManagerInterface $vsite_context_manager
   *   Vsite context manager.
   * @param \Drupal\vsite\Plugin\AppManangerInterface $app_mananger
   *   App manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, VsiteContextManagerInterface $vsite_context_manager, AppManangerInterface $app_mananger) {
    $this->configFactory = $config_factory;
    $this->vsiteContextManager = $vsite_context_manager;
    $this->appManager = $app_mananger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('vsite.context_manager'),
      $container->get('vsite.app.manager')
    );
  }

  /**
   * Checks if user has access to app's view page.
   *
   * It is different from `$this->access()` because it uses group permissions
   * as a fallback.
   * It is necessary that public app views return `AccessResultAllowed`.
   * Individual app view pages can work with `AccessResultNeutral`, because
   * there are `hook_entity_access()`'es which "allows" it later.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user.
   * @param string $app_name
   *   The app name.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   *
   * @see \Drupal\os_app_access\Access\AppAccess::access()
   * @see \os_bibcite_reference_access
   * @see \gnode_node_access
   */
  public function accessFromRouteMatch(AccountInterface $account, $app_name): AccessResultInterface {
    /** @var \Drupal\Core\Access\AccessResult $result */
    $result = AccessResult::neutral();
    /** @var \Drupal\group\Entity\GroupInterface|null $active_vsite */
    $active_vsite = $this->vsiteContextManager->getActiveVsite();

    if (!$active_vsite) {
      return $result;
    }

    /** @var \Drupal\Core\Config\ImmutableConfig $levels */
    $levels = $this->configFactory->get('os_app_access.access');
    /** @var int $access_level */
    $access_level = (int) $levels->get($app_name);

    if ($access_level === AppAccessLevels::DISABLED) {
      $result = AccessResult::forbidden('This App has been disabled.');

      return $this->cacheAccessResult($result);
    }

    // Check whether the user has access to all the bundles in app.
    /** @var array $group_permissions */
    $group_permissions = $this->appManager->getViewContentGroupPermissionsForApp($app_name);
    $default_access = TRUE;
    foreach ($group_permissions as $group_permission) {
      $default_access = ($default_access && $active_vsite->hasPermission($group_permission, $account));
    }

    if ($access_level === AppAccessLevels::PUBLIC) {
      $result = AccessResult::allowedIf($default_access);

      return $this->cacheAccessResult($result);
    }

    if ($access_level === AppAccessLevels::PRIVATE) {
      $default_access = ($default_access && $active_vsite->hasPermission('access private apps', $account));

      $result = AccessResult::forbidden();
      if ($default_access) {
        $result = AccessResult::allowed();
      }

      return $this->cacheAccessResult($result);
    }

    return $this->cacheAccessResult($result);
  }

  /**
   * Returns access.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user account.
   * @param string $app_name
   *   The app name.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   Access result.
   */
  public function access(AccountInterface $account, $app_name): AccessResultInterface {
    /** @var \Drupal\Core\Config\ImmutableConfig $levels */
    $levels = $this->configFactory->get('os_app_access.access');
    /** @var int $access_level */
    $access_level = (int) $levels->get($app_name);
    /** @var \Drupal\Core\Access\AccessResult $result */
    $result = AccessResult::neutral();

    if ($access_level === AppAccessLevels::PRIVATE) {
      /** @var \Drupal\group\Entity\GroupInterface|null $active_vsite */
      $active_vsite = $this->vsiteContextManager->getActiveVsite();

      if ($active_vsite) {
        if ($active_vsite->hasPermission('access private apps', $account)) {
          $result = AccessResult::allowed();
        }
        else {
          $result = AccessResult::forbidden();
        }
      }
    }
    if ($access_level === AppAccessLevels::DISABLED) {
      $result = AccessResult::forbidden('This App has been disabled.');
    }

    return $this->cacheAccessResult($result);
  }

  /**
   * Helper method to cache access result.
   *
   * @param \Drupal\Core\Access\AccessResult $access_result
   *   The access result to cache.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   The access result.
   */
  protected function cacheAccessResult(AccessResult $access_result): AccessResult {
    $access_result->addCacheTags(['app:access_changed']);
    $access_result->addCacheContexts(['vsite']);

    return $access_result;
  }

}
