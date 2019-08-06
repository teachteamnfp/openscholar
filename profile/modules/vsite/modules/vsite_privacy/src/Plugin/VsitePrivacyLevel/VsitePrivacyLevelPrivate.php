<?php

namespace Drupal\vsite_privacy\Plugin\VsitePrivacyLevel;

use Drupal\Component\Plugin\PluginBase;
use Drupal\vsite_privacy\Plugin\VsitePrivacyLevelInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Vsite privacy level.
 *
 * @VsitePrivacyLevel(
 *   title = @Translation("Site members only."),
 *   id = "private",
 *   description = @Translation("This setting can be useful during site creation. Your site will not be indexed by search engines."),
 *   weight = 1
 * )
 */
class VsitePrivacyLevelPrivate extends PluginBase implements VsitePrivacyLevelInterface {

  /**
   * {@inheritdoc}
   */
  public function checkAccess(AccountInterface $account): bool {
    if ($account->id() == 1) {
      return TRUE;
    }
    $roles = $account->getRoles();
    // If ($roles = support team or site member) return true
    // TODO: Revisit when role ids are determined.
    /* @var \Drupal\vsite\Plugin\VsiteContextManagerInterface $ctxManager */
    $ctxManager = \Drupal::service('vsite.context_manager');
    if ($group = $ctxManager->getActiveVsite()) {
      if ($member = $group->getMember($account)) {
        return TRUE;
      }
    }
    return FALSE;
  }

}
