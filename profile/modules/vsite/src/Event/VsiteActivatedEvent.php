<?php

namespace Drupal\vsite\Event;

use Drupal\group\Entity\GroupInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 *  Event fired when a vsite is activated
 */
class VsiteActivatedEvent extends Event {

  /**
   * @var \Drupal\group\Entity\GroupInterface
   */
  protected $group;

  /**
   * Constructor
   */
  public function __construct(GroupInterface $group) {
    $this->group = $group;
  }

  /**
   * Return the Group that represents the activated vsite
   *
   * @return \Drupal\group\Entity\GroupInterface The activated Vsite
   */
  public function getGroup() {
    return $this->group;
  }

}
