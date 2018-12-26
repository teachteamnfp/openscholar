<?php

namespace Drupal\vsite_privacy\Plugin\VsitePrivacyLevel;

use Drupal\Component\Plugin\PluginBase;
use Drupal\vsite_privacy\Plugin\VsitePrivacyLevelInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Vsite privacy level.
 *
 * @VsitePrivacyLevel(
 *   title = @Translation("Private"),
 *   id = "private",
 *   description = @Translation("Only accessible by site members."),
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
