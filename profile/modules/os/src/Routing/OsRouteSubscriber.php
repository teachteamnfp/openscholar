<?php

namespace Drupal\os\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to dynamic route events.
 */
class OsRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    $route = $collection->get('system.403');

    if ($route) {
      $route->setDefault('_controller', '\Drupal\os\Controller\Http403Controller::render');
    }
  }

}
