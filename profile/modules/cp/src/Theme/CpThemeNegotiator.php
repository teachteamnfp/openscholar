<?php

namespace Drupal\cp\Theme;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Theme\ThemeNegotiatorInterface;

class CpThemeNegotiator implements ThemeNegotiatorInterface {

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    $route_name = $route_match->getRouteName();
    $route = $route_match->getRouteObject();
    if (!$route) {
      return false;
    }

    if (strpos($route->getPath(), '/cp/') === 0) {
      return true;
    }

    if ($route->getOption('_admin_route') == 'true') {
      if (strpos($route->getPath(), '/node/') !== FALSE) {
        return true;
      }
      elseif (strpos($route->getPath(), '/bibcite/reference/') !== FALSE) {
        return true;
      }
    }

    return false;
  }

  /**
   * {@inheritdoc}
   */
  public function determineActiveTheme(RouteMatchInterface $route_match) {
    return 'os_admin';
  }
}