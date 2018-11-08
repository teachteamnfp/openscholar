<?php

namespace Drupal\vsite_privacy\Plugin\VsitePrivacyLevel;

use Drupal\Component\Plugin\PluginBase;
use Drupal\vsite_privacy\Plugin\VsitePrivacyLevelInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * @VsitePrivacyLevel(
 *   title = @Translation("Public"),
 *   id = "public",
 *   description = @Translation("Accessible to everyone.")
 * )
 */
class VsitePrivacyLevelPublic extends PluginBase implements VsitePrivacyLevelInterface {
  public function checkAccess (AccountInterface $account): bool {
    return true;
  }

}