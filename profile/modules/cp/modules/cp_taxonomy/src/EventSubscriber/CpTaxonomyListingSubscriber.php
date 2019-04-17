<?php

namespace Drupal\cp_taxonomy\EventSubscriber;

use Drupal\Core\Url;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class CpTaxonomyListingSubscriber.
 *
 * @package Drupal\cp_taxonomy\EventSubscriber
 */
class CpTaxonomyListingSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];
    $events[KernelEvents::VIEW][] = ['onView', 1000];
    return $events;
  }

  /**
   * Change out the Add Taxonomy link for our own.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent $e
   *   The event for the response.
   */
  public function onView(GetResponseForControllerResultEvent $e) {
    if ($e->getRequest()->attributes->get('_route') == 'cp.taxonomy') {
      $build_array = $e->getControllerResult();

      /** @var \Drupal\Core\StringTranslation\TranslatableMarkup $emptyMarkup */
      $emptyMarkup = $build_array['table']['#empty'];
      $url = Url::fromRoute('cp.taxonomy.add');
      $build_array['table']['#empty'] = t($emptyMarkup->getUntranslatedString(), [':link' => $url->toString()]);
      $e->setControllerResult($build_array);
    }
  }

}
