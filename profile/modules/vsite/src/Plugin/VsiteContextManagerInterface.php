<?php

namespace Drupal\vsite\Plugin;

use Drupal\group\Entity\GroupInterface;

/**
 *
 */
interface VsiteContextManagerInterface {

  /**
   *
   */
  public function activateVsite(GroupInterface $group);

  /**
   *
   */
  public function activateRoles();

  /**
   *
   */
  public function getActiveVsite() : ?GroupInterface;

  /**
   *
   */
  public function getActivePurl();

  /**
   *
   */
  public function getAbsoluteUrl(string $path = '');

  /**
   *
   */
  public function getStorage(GroupInterface $group = NULL);

}
