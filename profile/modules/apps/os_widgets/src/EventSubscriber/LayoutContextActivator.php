<?php

namespace Drupal\os_widgets\EventSubscriber;


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

    foreach ($contexts as $layoutContext) {
      $rules = $layoutContext->getActivationRules();
    }
  }

}
