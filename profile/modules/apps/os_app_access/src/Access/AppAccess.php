<?php

namespace Drupal\os_app_access\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\os_app_access\AppAccessLevels;
use Drupal\views\Views;
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
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The view page route.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function accessFromRouteMatch(RouteMatchInterface $route_match, AccountInterface $account): AccessResultInterface {
    $params = $route_match->getParameters();
    $view = Views::getView($params->get('view_id'));
    /** @var \Drupal\Core\Access\AccessResult $result */
    $result = AccessResult::neutral();
    /** @var \Drupal\group\Entity\GroupInterface|null $active_vsite */
    $active_vsite = $this->vsiteContextManager->getActiveVsite();

    if (!$active_vsite) {
      return $result;
    }

    $view->setDisplay($params->get('display_id'));
    /** @var \Drupal\os_app_access\Plugin\views\access\AppAccess $access_plugin */
    $access_plugin = $view->getDisplay()->getPlugin('access');
    /** @var \Drupal\Core\Config\ImmutableConfig $levels */
    $levels = $this->configFactory->get('os_app_access.access');
    /** @var int $access_level */
    $access_level = (int) $levels->get($access_plugin->options['app']);

    if ($access_level === AppAccessLevels::DISABLED) {
      $result = AccessResult::forbidden('This App has been disabled.');

      $result->addCacheTags(['app:access_changed']);
      $result->addCacheContexts(['vsite']);

      return $result;
    }

    // Check whether the user has access to all the bundles in app.
    /** @var array $group_permissions */
    $group_permissions = $this->appManager->getViewContentGroupPermissionsForApp($access_plugin->options['app']);
    $default_access = TRUE;
    foreach ($group_permissions as $group_permission) {
      $default_access = ($default_access && $active_vsite->hasPermission($group_permission, $account));
    }

    if ($access_level === AppAccessLevels::PUBLIC) {
      $result = AccessResult::allowedIf($default_access);

      $result->addCacheTags(['app:access_changed']);
      $result->addCacheContexts(['vsite']);

      return $result;
    }

    if ($access_level === AppAccessLevels::PRIVATE) {
      $default_access = ($default_access && $active_vsite->hasPermission('access private apps', $account));

      $result = AccessResult::forbidden();
      if ($default_access) {
        $result = AccessResult::allowed();
      }

      $result->addCacheTags(['app:access_changed']);
      $result->addCacheContexts(['vsite']);

      return $result;
    }

    $result->addCacheTags(['app:access_changed']);
    $result->addCacheContexts(['vsite']);

    return $result;
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

    $result->addCacheTags(['app:access_changed']);
    $result->addCacheContexts(['vsite']);

    return $result;
  }

}
