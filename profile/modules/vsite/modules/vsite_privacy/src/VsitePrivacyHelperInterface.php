<?php

namespace Drupal\vsite_privacy;

use Drupal\group\Entity\Group;

/**
 * VsitePrivacyHelperInterface.
 */
interface VsitePrivacyHelperInterface {

  /**
   * Update Robotstxt directives to vsite.
   *
   * @param \Drupal\group\Entity\Group $group
   *   Group entity.
   */
  public function updateRobotstxtDirectives(Group $group) : void;

  /**
   * Get Robotstxt directives.
   */
  public function getRobotstxtDirectives() : array;

}
