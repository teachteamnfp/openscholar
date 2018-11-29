<?php

namespace Drupal\vsite\Config;

use Drupal\vsite\Event\VsiteActivatedEvent;
use Drupal\vsite\VsiteEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 *
 */
class VsiteStorageDefinition implements EventSubscriberInterface {
  const VSITE_STORAGE = 0;

  /**
   * @var HierarchicalStorageInterface*/
  protected $hierarchicalStorage;

  /**
   *
   */
  public function __construct(HierarchicalStorageInterface $hierarchicalStorage) {
    $this->hierarchicalStorage = $hierarchicalStorage;
  }

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents() {
    $events = [];
    $events[VsiteEvents::VSITE_ACTIVATED][] = ['onVsiteActivated', 1];

    return $events;
  }

  /**
   *
   */
  public function onVsiteActivated(VsiteActivatedEvent $event) {
    $storage = $this->hierarchicalStorage->createCollection('vsite:' . $event->getGroup()->id());
    $this->hierarchicalStorage->addStorage($storage, self::VSITE_STORAGE);
  }

}
