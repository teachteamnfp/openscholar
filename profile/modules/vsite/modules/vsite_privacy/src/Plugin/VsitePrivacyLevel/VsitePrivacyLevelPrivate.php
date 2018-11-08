<?php

namespace Drupal\vsite_privacy\Plugin\VsitePrivacyLevel;


use Drupal\Component\Plugin\PluginBase;
use Drupal\vsite_privacy\Plugin\VsitePrivacyLevelInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * @VsitePrivacyLevel(
 *   title = @Translation("Private"),
 *   id = "private",
 *   description = @Translation("Only accessible by site members."),
 *   weight = 1
 * )
 */
class VsitePrivacyLevelPrivate extends PluginBase implements VsitePrivacyLevelInterface {

  public function checkAccess (AccountInterface $account): bool {
    if ($account->id() == 1) {
      return true;
    }
    $roles = $account->getRoles ();
    // if ($roles = support team or site member) return true
    // TODO: Revisit when role ids are determined
    return false;
  }
}