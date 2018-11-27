<?php
/**
 * Created by PhpStorm.
 * User: New User
 * Date: 10/8/2018
 * Time: 10:02 AM
 */

namespace Drupal\vsite\Plugin;

use Drupal\group\Entity\GroupInterface;

interface VsiteContextManagerInterface {

  public function activateVsite(GroupInterface $group);

  public function activateRoles();

  public function getActiveVsite() : ?GroupInterface;

  public function getActivePurl();

  public function getAbsoluteUrl(string $path = '');

  public function getStorage(GroupInterface $group = null);
}