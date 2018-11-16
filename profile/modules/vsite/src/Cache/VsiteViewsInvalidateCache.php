<?php
/**
 * Created by PhpStorm.
 * User: New User
 * Date: 9/17/2018
 * Time: 2:19 PM
 */

namespace Drupal\vsite\Cache;

use Drupal\Core\Cache\Cache;
use Drupal\vsite\Event\VsiteActivatedEvent;
use Drupal\vsite\VsiteEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class VsiteViewsInvalidateCache implements EventSubscriberInterface {

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents () {
    $events = [];
    $events[VsiteEvents::VSITE_ACTIVATED][] = ['onVsiteActivated', 1];

    return $events;
  }

  public function onVsiteActivated(VsiteActivatedEvent $event) {
    // @TODO: Called every views which has current_vsite_filter filter, find a better solution
    Cache::invalidateTags(['vsite:current_vsite_filter']);
  }
}