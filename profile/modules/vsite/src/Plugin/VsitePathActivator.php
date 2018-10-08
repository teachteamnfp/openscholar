<?php
/**
 * Created by PhpStorm.
 * User: New User
 * Date: 10/8/2018
 * Time: 10:48 AM
 */

namespace Drupal\vsite\Plugin;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\group\Context\GroupRouteContextTrait;
use Drupal\group\Entity\GroupInterface;
use Drupal\purl\PurlEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Drupal\purl\Event\ModifierMatchedEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;


class VsitePathActivator implements EventSubscriberInterface {
  use GroupRouteContextTrait;

  /** @var VsiteContextManagerInterface  */
  protected $vsiteContextManager;

  /** @var EntityTypeManagerInterface  */
  protected $entityTypeManager;

  public function __construct(VsiteContextManagerInterface $vsiteContextManager, EntityTypeManagerInterface $entityTypeManager) {
    $this->vsiteContextManager = $vsiteContextManager;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents () {
    $events = [];
    $events[PurlEvents::MODIFIER_MATCHED][] = ['onModifierMatched', 31];
    $events[KernelEvents::REQUEST][] = ['onRequest', 31]; // Price is Righting it to come in after RouterListener
    return $events;
  }

  public function onModifierMatched(ModifierMatchedEvent $event) {
    $id = $event->getValue ();
    /** @var GroupInterface $group */
    $group = $this->entityTypeManager->getStorage ('group')->load ($id);
    $this->vsiteContextManager->activateVsite ($group);
  }

  public function onRequest(GetResponseEvent $event) {
    if ($group = $this->getGroupFromRoute ()) {
      $this->vsiteContextManager->activateVsite ($group);
    }
  }
}