<?php
/**
 * Created by PhpStorm.
 * User: New User
 * Date: 9/17/2018
 * Time: 2:19 PM
 */

namespace Drupal\vsite\Config;


use Drupal\purl\Event\ModifierMatchedEvent;
use Drupal\purl\PurlEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class VsiteStorageDefinition implements EventSubscriberInterface {
  const VSITE_STORAGE = 0;

  /** @var HierarchicalStorageInterface */
  protected $hierarchicalStorage;

  public function __construct(HierarchicalStorageInterface $hierarchicalStorage) {
    $this->hierarchicalStorage = $hierarchicalStorage;
  }

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents () {
    $events = [];
    $events[PurlEvents::MODIFIER_MATCHED][] = ['onModifierMatched', 255];
    return $events;
  }

  public function onModifierMatched(ModifierMatchedEvent $event) {
    $modifier = $event->getModifier ();
    $collection = $this->hierarchicalStorage->createCollection ('vsite.'.$modifier);
    $this->hierarchicalStorage->addStorage($collection, self::VSITE_STORAGE);
  }
}