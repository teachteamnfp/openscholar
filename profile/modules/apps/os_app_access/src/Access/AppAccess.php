<?php
/**
 * Created by PhpStorm.
 * User: New User
 * Date: 11/6/2018
 * Time: 2:39 PM
 */

namespace Drupal\os_app_access\Access;


use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultNeutral;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\os_app_access\AppAccessLevels;
use Drupal\views\Views;

class AppAccess implements AccessInterface {

  public function accessFromRouteMatch(RouteMatchInterface $route_match, AccountProxyInterface $account) {
    $params = $route_match->getParameters ();
    $view = Views::getView ($params->get('view_id'));
    if ($view->setDisplay ($params->get('display_id'))) {
      $access = $view->getDisplay()->getPlugin ('access');
      if ($access instanceof \Drupal\os_app_access\Plugin\views\access\AppAccess) {
        return $this->access($account, $access->options['app']);
      }
    }

    return new AccessResultNeutral();
  }

  public function access(AccountInterface $account, $app_name) {
    /** @var ConfigFactoryInterface $config_factory */
    $config_factory = \Drupal::service('config.factory'); // TODO: Dependency Inject in constructor
    $levels = $config_factory->get('app.access');
    $access_level = $levels->get($app_name);
    /** @var AccessResult $result */
    $result = AccessResult::neutral ();
    if ($access_level == AppAccessLevels::PUBLIC) {
      $result = AccessResult::allowed ();
    }
    if ($access_level == AppAccessLevels::PRIVATE) {
      $result = AccessResult::allowedIfHasPermission ($account, 'Access private apps');
      if ($result->isNeutral ()) {
        $result = AccessResult::forbidden ();
      }
    }
    if ($access_level == AppAccessLevels::DISABLED) {
      $result = AccessResult::forbidden(t('This App has been disabled.'));
    }
    $result->addCacheTags (['app:access_changed']);
    return $result;
  }
}