<?php

namespace Drupal\vsite\Config;

use Drupal\vsite\Event\VsiteActivatedEvent;
use Drupal\vsite\VsiteEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Sets the ConfigStorage for a vsite when it's activated.
 */
class VsiteStorageDefinition implements EventSubscriberInterface {

  public const ACTIVATED_VSITE_WEIGHT_KEY = 'activated_vsite_weight_key';

  /**
   * The storage element to add a vsite storage to.
   *
   * @var \Drupal\vsite\Config\HierarchicalStorageInterface
   */
  protected $hierarchicalStorage;

  /**
   * Constructor.
   */
  public function __construct(HierarchicalStorageInterface $hierarchicalStorage) {
    $this->hierarchicalStorage = $hierarchicalStorage;
  }

  /**
   * List all events to listen for.
   *
   * @inheritdoc
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
    $vsite_weight = &drupal_static(self::ACTIVATED_VSITE_WEIGHT_KEY, 0);
    $storage = $this->hierarchicalStorage->createCollection('vsite:' . $event->getGroup()->id());
    $this->hierarchicalStorage->addStorage($storage, $vsite_weight--);
  }

}
