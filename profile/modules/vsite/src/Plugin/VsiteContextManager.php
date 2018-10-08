<?php
/**
 * Created by PhpStorm.
 * User: New User
 * Date: 10/8/2018
 * Time: 10:01 AM
 */

namespace Drupal\vsite\Plugin;


use Drupal\group\Entity\GroupInterface;
use Drupal\vsite\Event\VsiteActivatedEvent;
use Drupal\vsite\VsiteEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class VsiteContextManager implements VsiteContextManagerInterface {

  /** @var GroupInterface */
  protected $activeGroup;

  /** @var EventDispatcherInterface  */
  protected $dispatcher;

  public function __construct(EventDispatcherInterface $dispatcher) {
    $this->dispatcher = $dispatcher;
  }

  public function activateVsite (GroupInterface $group) {
    // TODO: Implement activateVsite() method.
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

  public function getAbsoluteUrl (string $path) {
    // TODO: Implement getAbsoluteUrl() method.
  }

}