<?php

namespace Drupal\vsite_privacy\Plugin;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Interface VsitePrivacyLevelManagerInterface.
 */
interface VsitePrivacyLevelManagerInterface extends PluginManagerInterface {

  /**
   * Returns options.
   */
  public function getOptions();

  /**
   * Returns descriptions.
   */
  public function getDescriptions();

  /**
   * Checks access for a user.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user account.
   * @param string $plugin_id
   *   Plugin id.
   */
  public function checkAccessForPlugin(AccountInterface $account, string $plugin_id);

}
