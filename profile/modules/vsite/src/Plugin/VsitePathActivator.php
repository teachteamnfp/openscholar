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
use Drupal\group\Entity\Storage\GroupContentStorageInterface;
use Drupal\purl\PurlEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Drupal\purl\Event\ModifierMatchedEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;


class VsitePathActivator implements EventSubscriberInterface {
  use GroupRouteContextTrait {
    getGroupFromRoute as traitGetGroupFromRoute;
  }

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

  /**
   * Retrieves the group entity from the current route.
   *
   * This will try to load the group entity from the route if present. If we are
   * on the group add form, it will return a new group entity with the group
   * type set.
   *
   * @return \Drupal\group\Entity\GroupInterface|null
   *   A group entity if one could be found or created, NULL otherwise.
   */
  public function getGroupFromRoute() {
    // Gets everything except groupContent alone.
    $group = $this->traitGetGroupFromRoute();
    if ($group) {
      return $group;
    }

    $route_match = $this->getCurrentRouteMatch();

    if ($node = $route_match->getParameter('node')) {
      /** @var GroupContentStorageInterface $storage */
      $storage = \Drupal::entityTypeManager ()->getStorage ('group_content');
      // Loads all groups with a relation to the node
      $group_content = $storage->loadByEntity ($node);
      if (count ($group_content)) {
        // Return the first group associated with this content, assuming we are limiting to 1?
        $group = current ($group_content)->getGroup ();
        return $group;
      }
    }

    return NULL;
  }
}