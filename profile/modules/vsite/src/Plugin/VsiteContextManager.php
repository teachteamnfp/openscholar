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
 * class responds and dispatches an event for other modules to listen to.
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
   * Alias manager.
   *
   * @var \Drupal\Core\Path\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * Constructor.
   *
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
   *   The event dispatcher.
   */
  public function __construct(EventDispatcherInterface $dispatcher) {
    $this->dispatcher = $dispatcher;
  }

  /**
   * {@inheritdoc}
   */
  public function activateVsite(GroupInterface $group) {
    if (!$group->id()) {
      return;
    }

    $this->activeGroup = $group;
    $this->activateRoles();

    $event = new VsiteActivatedEvent($group);
    $this->dispatcher->dispatch(VsiteEvents::VSITE_ACTIVATED, $event);
  }

  /**
   * {@inheritdoc}
   */
  public function activateRoles() {
    // TODO: Implement activateRoles() method.
  }

  /**
   * {@inheritdoc}
   */
  public function getActiveVsite() : ?GroupInterface {
    return $this->activeGroup;
  }

  /**
   * {@inheritdoc}
   */
  public function getActivePurl() {
    if (!empty($this->activeGroup)) {
      return trim($this->aliasManager->getAliasByPath('/group/' . $this->activeGroup->id()), '/');
    }
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function getAbsoluteUrl(string $path = '', GroupInterface $group = NULL) {
    if (!$this->activeGroup) {
      return $path;
    }

    $purl = $this->activeGroup->toUrl('canonical', ['base_url' => ''])->toString();
    return $purl . '/' . ltrim($path, '/');
  }

  /**
   * {@inheritdoc}
   */
  public function getStorage(GroupInterface $group = NULL) {}

}
