<?php

namespace Drupal\vsite_privacy\Plugin;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Session\AccountInterface;

interface VsitePrivacyLevelManagerInterface extends PluginManagerInterface {

  public function getOptions();

  public function getDescriptions();

  public function checkAccessForPlugin(AccountInterface $account, string $plugin_id);
}