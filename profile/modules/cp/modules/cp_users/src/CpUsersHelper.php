<?php

namespace Drupal\cp_users;

use Drupal\Core\Session\AccountInterface;
use Drupal\group\Entity\GroupInterface;

/**
 * Helper methods for cp_users.
 */
class CpUsersHelper implements CpUsersHelperInterface {

  /**
   * {@inheritdoc}
   */
  public function isVsiteOwner(GroupInterface $vsite, AccountInterface $account): bool {
    return ($vsite->getOwnerId() === $account->id());
  }

}
