<?php

namespace Drupal\vsite\Plugin;

use Drupal\group\Entity\GroupInterface;
use Drupal\vsite\Event\VsiteActivatedEvent;
use Drupal\vsite\VsiteEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Manages and stores active vsites.
 *
 * Other classes declare a vsite is active to this manager, and this
 *   class responds and dispatches an event for other modules to listen to.
 */
class VsiteContextManager implements VsiteContextManagerInterface {

  /**
   * The active vsite.
   *
   * @var \Drupal\group\Entity\GroupInterface
   */
  protected $activeGroup = NULL;

  /**
   * Event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $dispatcher;

  /**
   * Constructor.
   */
  public function __construct(EventDispatcherInterface $dispatcher) {
    $this->dispatcher = $dispatcher;
  }

  /**
   * Activate a vsite and handle all processing that entails.
   */
  public function activateVsite(GroupInterface $group) {
    $this->activeGroup = $group;
    $this->activateRoles();

    $event = new VsiteActivatedEvent($group);
    $this->dispatcher->dispatch(VsiteEvents::VSITE_ACTIVATED, $event);
  }

  /**
   * Activate the user roles a given user should have within the active vsite.
   */
  public function activateRoles() {
    // TODO: Implement activateRoles() method.
  }

  /**
   * Returns the active vsite.
   *
   * @return \Drupal\group\Entity\GroupInterface
   *   The active vsite.
   */
  public function getActiveVsite() : ?GroupInterface {
    return $this->activeGroup;
  }

  /**
   * Returns just the purl for the active vsite.
   */
  public function getActivePurl() {
    if (!empty($this->activeGroup)) {
      return trim(\Drupal::service('path.alias_manager')->getAliasByPath('/group/' . $this->activeGroup->id()), '/');
    }
    return '';
  }

  /**
   * Gets an absolute url to a vsite.
   */
  public function getAbsoluteUrl(string $path = '', GroupInterface $group = NULL) {
    // TODO: Implement getAbsoluteUrl() method.
    // 1. Generate modifier based on Group given
    // 2. Apply it to path or route.
    $purl = $this->activeGroup->toUrl('canonical', ['base_url' => ''])->toString();
    return $purl . '/' . ltrim($path, '/');
  }

  /**
   * Returns the ConfigStorage for the given vsite.
   */
  public function getStorage(GroupInterface $group = NULL) {

  }

}
