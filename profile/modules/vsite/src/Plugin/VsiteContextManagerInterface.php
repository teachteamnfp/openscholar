<?php

namespace Drupal\vsite\Plugin;

use Drupal\group\Entity\GroupInterface;

/**
 * Interface for the VsiteContextManager class.
 */
interface VsiteContextManagerInterface {

  /**
   * Activate the vsite represented by the given group.
   */
  public function activateVsite(GroupInterface $group);

  /**
   * Activate the user's roles within the active vsite.
   */
  public function activateRoles();

  /**
   * Return the active vsite.
   */
  public function getActiveVsite() : ?GroupInterface;

  /**
   * Return the purl for the active vsite.
   */
  public function getActivePurl();

  /**
   * Get an absolute url a vsite.
   */
  public function getAbsoluteUrl(string $path = '');

  /**
   * Return the StorageInterface object for a vsite.
   */
  public function getStorage(GroupInterface $group = NULL);

}
