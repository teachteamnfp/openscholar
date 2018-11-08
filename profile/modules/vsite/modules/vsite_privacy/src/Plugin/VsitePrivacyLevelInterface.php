<?php

namespace Drupal\vsite_privacy\Plugin;

interface VsitePrivacyLevelInterface {

  public function checkAccess(\Drupal\Core\Session\AccountInterface $account) : bool;

}