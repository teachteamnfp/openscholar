<?php

namespace Drupal\cp_users\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\cp_users\CpRolesHelperInterface;
use Drupal\group\Entity\GroupRoleInterface;
use Drupal\group\Entity\GroupTypeInterface;
use Drupal\vsite\Plugin\VsiteContextManagerInterface;

/**
 * Access checker for performing operations on cp roles.
 */
class CpRolesOperationAccessCheck implements AccessInterface {

  /**
   * CpRoles helper service.
   *
   * @var \Drupal\cp_users\CpRolesHelperInterface
   */
  protected $cpRolesHelper;

  /**
   * Vsite context manager.
   *
   * @var \Drupal\vsite\Plugin\VsiteContextManagerInterface
   */
  protected $vsiteContextManager;

  /**
   * Creates a new CpRolesOperationAccessCheck object.
   *
   * @param \Drupal\cp_users\CpRolesHelperInterface $cp_roles_helper
   *   CpRoles helper service.
   * @param \Drupal\vsite\Plugin\VsiteContextManagerInterface $vsite_context_manager
   *   Vsite context manager.
   */
  public function __construct(CpRolesHelperInterface $cp_roles_helper, VsiteContextManagerInterface $vsite_context_manager) {
    $this->cpRolesHelper = $cp_roles_helper;
    $this->vsiteContextManager = $vsite_context_manager;
  }

  /**
   * Checks whether the user has access to perform operation on cp role.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The account to check access for.
   * @param \Drupal\group\Entity\GroupRoleInterface $group_role
   *   The cp role.
   * @param \Drupal\group\Entity\GroupTypeInterface $group_type
   *   The group type.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access(AccountInterface $account, GroupRoleInterface $group_role, GroupTypeInterface $group_type): AccessResultInterface {
    /** @var \Drupal\group\Entity\GroupInterface|null $active_vsite */
    $active_vsite = $this->vsiteContextManager->getActiveVsite();

    if (!$active_vsite) {
      return AccessResult::forbidden();
    }

    if ($this->cpRolesHelper->isDefaultGroupRole($group_role) && !$account->hasPermission('manage default group roles')) {
      return AccessResult::forbidden();
    }

    if (!$active_vsite->hasPermission('manage cp roles', $account)) {
      return AccessResult::forbidden();
    }

    return AccessResult::allowed();
  }

}
