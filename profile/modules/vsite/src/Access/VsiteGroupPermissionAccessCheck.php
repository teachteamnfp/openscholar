<?php

namespace Drupal\vsite\Access;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\Routing\Route;
use Drupal\group\Access\GroupPermissionAccessCheck;

/**
 * Add the vsite to the RouteMatch's parameter set if one is active
 */
class VsiteGroupPermissionAccessCheck extends GroupPermissionAccessCheck {

  /**
   * Checks access.
   *
   * @param \Symfony\Component\Routing\Route $route
   *   The route to check against.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The parametrized route.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The account to check access for.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access(Route $route, RouteMatchInterface $route_match, AccountInterface $account) {
    /** @var \Drupal\group\Entity\Group $group */
    if ($group = \Drupal::service('vsite.context_manager')->getActiveVsite()) {
      $current_route_match = \Drupal::service('current_route_match');
      $currentParameter = $current_route_match->getParameters();
      $parameterBag = $route_match->getParameters();
      $parameterBag->add(['group' => $group]);
      $currentParameter->add(['group' => $group]);
    }
    return parent::access($route, $route_match, $account);
  }

}
