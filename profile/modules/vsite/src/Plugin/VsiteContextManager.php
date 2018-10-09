<?php
/**
 * Created by PhpStorm.
 * User: New User
 * Date: 10/8/2018
 * Time: 10:01 AM
 */

namespace Drupal\vsite\Plugin;


use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\group\Entity\GroupInterface;
use Drupal\vsite\Event\VsiteActivatedEvent;
use Drupal\vsite\VsiteEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class VsiteContextManager implements VsiteContextManagerInterface {

  /** @var GroupInterface */
  protected $activeGroup;

  /** @var EventDispatcherInterface  */
  protected $dispatcher;

  /** @var UrlGeneratorInterface  */
  protected $urlGenerator;

  public function __construct(EventDispatcherInterface $dispatcher) {
    $this->dispatcher = $dispatcher;
   // $this->urlGenerator = $urlGenerator;
  }

  public function activateVsite (GroupInterface $group) {
    $this->activeGroup = $group;
    $this->activateRoles ();

    $event = new VsiteActivatedEvent($group);
    $this->dispatcher->dispatch (VsiteEvents::VSITE_ACTIVATED);
  }

  public function activateRoles () {
    // TODO: Implement activateRoles() method.
  }

  public function getActiveVsite () {
    return $this->activeGroup;
  }

  public function getAbsoluteUrl (string $path = '', GroupInterface $group = null) {
    // TODO: Implement getAbsoluteUrl() method.
    // 1. Generate modifier based on Group given
    // 2. Apply it to path or route
  }

  public function getStorage(GroupInterface $group = null) {

  }

}