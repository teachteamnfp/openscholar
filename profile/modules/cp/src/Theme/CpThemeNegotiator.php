<?php

namespace Drupal\cp\Theme;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Theme\ThemeNegotiatorInterface;

/**
 * Activate the CP theme on cp pages and certain admin routes.
 */
class CpThemeNegotiator implements ThemeNegotiatorInterface {

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    $route_name = $route_match->getRouteName();
    $route = $route_match->getRouteObject();
    if (!$route) {
      return FALSE;
    }

    if (strpos($route->getPath(), '/cp/') === 0) {
      return TRUE;
    }

    if ($route->getOption('_admin_route') == 'true') {
      if (strpos($route->getPath(), '/node/') !== FALSE) {
        return TRUE;
      }
      elseif (strpos($route->getPath(), '/bibcite/reference/') !== FALSE) {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function determineActiveTheme(RouteMatchInterface $route_match) {
    return 'os_admin';
  }

}
