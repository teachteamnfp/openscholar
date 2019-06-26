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
  const PRESET_STORAGE = -10;

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
    $storage = $this->hierarchicalStorage->createCollection('vsite:' . $event->getGroup()->id());
    $this->hierarchicalStorage->addStorage($storage, self::VSITE_STORAGE);

    if ($event->getGroup()->hasField('field_preset') && !$event->getGroup()->get('field_preset')->isEmpty()) {
      $preset_id = $event->getGroup()->get('field_preset')->get(0)->getValue()['target_id'];
      /** @var \Drupal\vsite\Entity\GroupPresetInterface $preset */
      if ($preset = \Drupal::entityTypeManager()->getStorage('group_preset')->load($preset_id)) {
        $this->hierarchicalStorage->addStorage($preset->getPresetStorage(), self::PRESET_STORAGE);
      }
    }
  }

}
