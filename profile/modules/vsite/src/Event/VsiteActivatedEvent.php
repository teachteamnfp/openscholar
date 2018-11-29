<?php

namespace Drupal\vsite\Event;

use Drupal\group\Entity\GroupInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 *
 */
class VsiteActivatedEvent extends Event {

  /**
   * @var \Drupal\group\Entity\GroupInterface*/
  protected $group;

  /**
   *
   */
  public function __construct(GroupInterface $group) {
    $this->group = $group;
  }

  /**
   * @return \Drupal\group\Entity\GroupInterface
   */
  public function getGroup() {
    return $this->group;
  }

}
