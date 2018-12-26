<?php

namespace Drupal\vsite_privacy\Plugin;

use Drupal\Core\Session\AccountInterface;

/**
 * Interface VsitePrivacyLevelInterface.
 */
interface VsitePrivacyLevelInterface {

  /**
   * Checks access for a user.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user account.
   *
   * @return bool
   *   TRUE if allowed, otherwise FALSE.
   */
  public function checkAccess(AccountInterface $account) : bool;

}
