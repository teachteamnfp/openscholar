<?php

namespace Drupal\vsite\Listener;

use Drupal\Core\Plugin\Context\ContextProviderInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Route;

class VsiteGroupIdListener implements EventSubscriberInterface {

  /**
   * @var \Drupal\Core\Plugin\Context\ContextProviderInterface
   */
  private $context_provider;

  public function __construct(ContextProviderInterface $context_provider) {
    $this->context_provider = $context_provider;
  }

  /**
   * Returns an array of event names this subscriber wants to listen to.
   *
   * The array keys are event names and the value can be:
   *
   *  * The method name to call (priority defaults to 0)
   *  * An array composed of the method name to call and the priority
   *  * An array of arrays composed of the method names to call and respective
   *    priorities, or 0 if unset
   *
   * For instance:
   *
   *  * array('eventName' => 'methodName')
   *  * array('eventName' => array('methodName', $priority))
   *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
   *
   * @return array The event names to listen to
   */
  public static function getSubscribedEvents() {
    return array(
      KernelEvents::REQUEST => ['onKernelRequest', 33],
    );
  }

  public function onKernelRequest(GetResponseEvent $event)
  {
    $request = $event->getRequest();

    $requestUri = $request->getRequestUri();
    if ($requestUri != '/cp/content') {
      return;
    }
    $current_route_match = \Drupal::service('current_route_match');
    $currentParameter = $current_route_match->getParameters();
    /** @var \Drupal\group\Entity\Group $group */
    if ($group = \Drupal::service('vsite.context_manager')->getActiveVsite()) {
      $currentParameter->add(['group' => $group]);
    }
  }
}
