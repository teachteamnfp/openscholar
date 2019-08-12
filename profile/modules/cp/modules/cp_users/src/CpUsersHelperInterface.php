<?php

namespace Drupal\cp_users;

use Drupal\Core\Session\AccountInterface;
use Drupal\group\Entity\GroupInterface;

/**
 * Helper methods for cp_users.
 */
interface CpUsersHelperInterface {

  /**
   * Checks if the user is owner of a vsite.
   *
   * @param \Drupal\group\Entity\GroupInterface $vsite
   *   The vsite.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user.
   *
   * @return bool
   *   TRUE if is an owner. Otherwise FALSE.
   */
  public function isVsiteOwner(GroupInterface $vsite, AccountInterface $account): bool;

}
