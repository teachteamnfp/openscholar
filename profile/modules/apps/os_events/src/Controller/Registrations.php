<?php

namespace Drupal\os_events\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\views\Views;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Registrations.
 *
 * @package Drupal\os_events\Controller
 */
class Registrations extends ControllerBase {

  /**
   * The route match service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $route;

  /**
   * Registrations constructor.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route
   *   The route match service.
   */
  public function __construct(RouteMatchInterface $route) {
    $this->route = $route;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
        $container->get('current_route_match')
    );
  }

  /**
   * Registration list view builder.
   *
   * @return array|null
   *   The view itself.
   */
  public function listBuilder() {

    $content = [];
    $node = $this->route->getParameter('node');
    $nid = $node->id();
    $view = Views::getView('rng_registrations_node');
    if (is_object($view)) {
      $view->setArguments([$nid]);
      $view->setDisplay('page_1');
      $view->preExecute();
      $view->execute();
      $content = $view->buildRenderable('page_1', [$nid]);
    }
    return $content;
  }

}
