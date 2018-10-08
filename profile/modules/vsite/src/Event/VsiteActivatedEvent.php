<?php
/**
 * Created by PhpStorm.
 * User: New User
 * Date: 10/8/2018
 * Time: 10:27 AM
 */
namespace Drupal\vsite\Event;

use Drupal\group\Entity\GroupInterface;
use Symfony\Component\EventDispatcher\Event;

class VsiteActivatedEvent extends Event {

  /** @var GroupInterface  */
  protected $group;

  public function __construct(GroupInterface $group) {
    $this->group = $group;
  }

  /**
   * @return GroupInterface
   */
  public function getGroup() {
    return $this->group;
  }
}