<?php

namespace Drupal\vsite_privacy\Plugin\VsitePrivacyLevel;

use Drupal\Component\Plugin\PluginBase;
use Drupal\vsite_privacy\Plugin\VsitePrivacyLevelInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Vsite privacy level.
 *
 * @VsitePrivacyLevel(
 *   title = @Translation("Anyone with the link."),
 *   id = "unindexed",
 *   description = @Translation("Anyone who has the URL to your site can view your site. Your site will not be indexed by search engines."),
 *   weight = 2
 * )
 */
class VsitePrivacyLevelUnindexed extends PluginBase implements VsitePrivacyLevelInterface {

  /**
   * {@inheritdoc}
   */
  public function checkAccess(AccountInterface $account): bool {
    return TRUE;
  }

}
