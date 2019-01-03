<?php

namespace Drupal\os_app_access\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultNeutral;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\os_app_access\AppAccessLevels;
use Drupal\views\Views;

/**
 * Class AppAccess.
 */
class AppAccess implements AccessInterface {

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
  public function access(AccountInterface $account, $app_name) {
    /** @var \Drupal\Core\Config\ConfigFactoryInterface $config_factory */
    // TODO: Dependency Inject in constructor.
    $config_factory = \Drupal::service('config.factory');
    $levels = $config_factory->get('app.access');
    $access_level = $levels->get($app_name);
    /** @var \Drupal\Core\Access\AccessResult $result */
    $result = AccessResult::neutral();
    if ($access_level == AppAccessLevels::PUBLIC) {
      $result = AccessResult::allowed();
    }
    if ($access_level == AppAccessLevels::PRIVATE) {
      $result = AccessResult::allowedIfHasPermission($account, 'access private apps');
      if ($result->isNeutral()) {
        $result = AccessResult::forbidden();
      }
    }
    if ($access_level == AppAccessLevels::DISABLED) {
      $result = AccessResult::forbidden(t('This App has been disabled.'));
    }
    $result->addCacheTags(['app:access_changed']);
    return $result;
  }

}
