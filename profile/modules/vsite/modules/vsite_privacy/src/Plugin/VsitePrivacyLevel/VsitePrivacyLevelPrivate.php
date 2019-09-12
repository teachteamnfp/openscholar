<?php

namespace Drupal\vsite_privacy\Plugin\VsitePrivacyLevel;

use Drupal\Core\Session\AccountInterface;
use Drupal\vsite_privacy\Plugin\VsitePrivacyLevelPluginBase;

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
class VsitePrivacyLevelPrivate extends VsitePrivacyLevelPluginBase {

  /**
   * {@inheritdoc}
   */
  public function checkAccess(AccountInterface $account): bool {
    $access = parent::checkAccess($account);

    /** @var \Drupal\group\Entity\GroupInterface|null $active_vsite */
    $active_vsite = $this->vsiteContextManager->getActiveVsite();

    if (!$active_vsite || $account->isAnonymous()) {
      return FALSE;
    }

    $vsite_membership = $active_vsite->getMember($account);

    return ($access && $vsite_membership);
  }

}
