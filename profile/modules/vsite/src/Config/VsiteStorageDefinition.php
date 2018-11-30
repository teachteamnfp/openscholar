<?php

namespace Drupal\vsite\Config;

use Drupal\vsite\Event\VsiteActivatedEvent;
use Drupal\vsite\VsiteEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Sets the ConfigStorage for a vsite when it's activated.
 */
class VsiteStorageDefinition implements EventSubscriberInterface {
  const VSITE_STORAGE = 0;

  /**
   * @var HierarchicalStorageInterface
   */
  protected $hierarchicalStorage;

  /**
   * Constructor.
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
   * Listener for VsiteActivatedEvent.
   */
  public function onVsiteActivated(VsiteActivatedEvent $event) {
    $storage = $this->hierarchicalStorage->createCollection('vsite:' . $event->getGroup()->id());
    $this->hierarchicalStorage->addStorage($storage, self::VSITE_STORAGE);
  }

}
