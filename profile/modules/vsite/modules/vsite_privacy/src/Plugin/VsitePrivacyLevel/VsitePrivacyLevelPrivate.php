<?php

namespace Drupal\vsite_privacy\Plugin\VsitePrivacyLevel;


use Drupal\Component\Plugin\PluginBase;
use Drupal\vsite_privacy\Plugin\VsitePrivacyLevelInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * @VsitePrivacyLevel(
 *   title = @Translation("Private"),
 *   id = "private",
 *   description = @Translation("Only accessible by site members.")
 * )
 */
class VsitePrivacyLevelPrivate extends PluginBase implements VsitePrivacyLevelInterface {

  public function checkAccess (AccountInterface $account): bool {
    return false;
  }
}