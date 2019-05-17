<?php

namespace Drupal\os_widgets\EventSubscriber;


use Drupal\Core\Routing\RouteMatch;
use Drupal\os_widgets\LayoutContextInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class LayoutContextActivator implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];

    $events[KernelEvents::REQUEST][] = ['onRequest', 30];

    return $events;
  }

  public function onRequest(KernelEvent $event) {
    /** @var LayoutContextInterface[] $contexts */
    $contexts = \Drupal::entityTypeManager()->getStorage('layout_context')->loadMultiple();
    $request = $event->getRequest();
    $routeMatch = RouteMatch::createFromRequest($request);
    $route = $routeMatch->getRouteObject();

    foreach ($contexts as $layoutContext) {
      $rules = $layoutContext->getActivationRules();
      $lines = preg_split("|[\n\r]", $rules);
      foreach ($lines as $l) {
        $regex = $this->convertRuleToRegex($l);
        if (preg_match($regex, $route->getPath()) || preg_match($regex, $routeMatch->getRouteName())) {
          // what do I do here
        }
      }
    }
  }

  protected function convertRuleToRegex($rule) {
    return '|^'.str_replace('*', '.*', $rule).'$|';
  }

}
