<?php

namespace Drupal\vsite_privacy\Plugin\VsitePrivacyLevel;

use Drupal\Component\Plugin\PluginBase;
use Drupal\vsite_privacy\Plugin\VsitePrivacyLevelInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Vsite privacy level.
 *
 * @VsitePrivacyLevel(
 *   title = @Translation("Public"),
 *   id = "public",
 *   description = @Translation("Accessible to everyone."),
 *   weight = -1000
 * )
 */
class VsitePrivacyLevelPublic extends PluginBase implements VsitePrivacyLevelInterface {

  /**
   * {@inheritdoc}
   */
  public function checkAccess(AccountInterface $account): bool {
    return TRUE;
  }

}
