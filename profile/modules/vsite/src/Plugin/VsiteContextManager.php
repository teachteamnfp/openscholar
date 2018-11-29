<?php

namespace Drupal\vsite\Plugin;

use Drupal\group\Entity\GroupInterface;
use Drupal\vsite\Event\VsiteActivatedEvent;
use Drupal\vsite\VsiteEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 *
 */
class VsiteContextManager implements VsiteContextManagerInterface {

  /**
   * @var \Drupal\group\Entity\GroupInterface*/
  protected $activeGroup = NULL;

  /**
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface*/
  protected $dispatcher;

  /**
   *
   */
  public function __construct(EventDispatcherInterface $dispatcher) {
    $this->dispatcher = $dispatcher;
  }

  /**
   *
   */
  public function activateVsite(GroupInterface $group) {
    $this->activeGroup = $group;
    $this->activateRoles();

    $event = new VsiteActivatedEvent($group);
    $this->dispatcher->dispatch(VsiteEvents::VSITE_ACTIVATED, $event);
  }

  /**
   *
   */
  public function activateRoles() {
    // TODO: Implement activateRoles() method.
  }

  /**
   * @return \Drupal\group\Entity\GroupInterface
   */
  public function getActiveVsite() : ?GroupInterface {
    return $this->activeGroup;
  }

  /**
   *
   */
  public function getActivePurl() {
    if (!empty($this->activeGroup)) {
      return trim(\Drupal::service('path.alias_manager')->getAliasByPath('/group/' . $this->activeGroup->id()), '/');
    }
    return '';
  }

  /**
   *
   */
  public function getAbsoluteUrl(string $path = '', GroupInterface $group = NULL) {
    // TODO: Implement getAbsoluteUrl() method.
    // 1. Generate modifier based on Group given
    // 2. Apply it to path or route.
    $purl = $this->activeGroup->toUrl('canonical', ['base_url' => ''])->toString();
    return $purl . '/' . ltrim($path, '/');
  }

  /**
   *
   */
  public function getStorage(GroupInterface $group = NULL) {

  }

}
