<?php

namespace Drupal\os_app_access\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Access\AccessResultNeutral;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\os_app_access\AppAccessLevels;
use Drupal\views\Views;
use Drupal\vsite\Plugin\VsiteContextManagerInterface;

/**
 * Class AppAccess.
 */
class AppAccess implements AccessInterface {

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
   * Creates a new AppAccess object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory service.
   * @param \Drupal\vsite\Plugin\VsiteContextManagerInterface $vsite_context_manager
   *   Vsite context manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, VsiteContextManagerInterface $vsite_context_manager) {
    $this->configFactory = $config_factory;
    $this->vsiteContextManager = $vsite_context_manager;
  }

  /**
   * Access route match.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   Route match.
   * @param \Drupal\Core\Session\AccountProxyInterface $account
   *   The user account.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   Access result.
   */
  public function accessFromRouteMatch(RouteMatchInterface $route_match, AccountProxyInterface $account) {
    $params = $route_match->getParameters();
    $view = Views::getView($params->get('view_id'));
    if ($view->setDisplay($params->get('display_id'))) {
      $access = $view->getDisplay()->getPlugin('access');
      if ($access instanceof AppAccess) {
        return $this->access($account, $access->options['app']);
      }
    }

    return new AccessResultNeutral();
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

    if ($access_level === AppAccessLevels::PUBLIC) {
      $result = AccessResult::neutral();
    }
    if ($access_level === AppAccessLevels::PRIVATE) {
      /** @var \Drupal\group\Entity\GroupInterface|null $active_vsite */
      $active_vsite = $this->vsiteContextManager->getActiveVsite();

      if (!$active_vsite) {
        $result = AccessResult::neutral();
      }
      elseif (!$active_vsite->hasPermission('access private apps', $account)) {
        $result = AccessResult::forbidden();
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
