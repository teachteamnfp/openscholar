<?php

namespace Drupal\cp_users\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\vsite\Plugin\VsiteContextManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Checks whether a user can change a vsite ownership.
 */
class ChangeOwnershipAccessCheck implements AccessInterface, ContainerInjectionInterface {

  /**
   * Vsite context manager.
   *
   * @var \Drupal\vsite\Plugin\VsiteContextManagerInterface
   */
  protected $vsiteContextManager;

  /**
   * Creates a new ChangeOwnershipAccessCheck object.
   *
   * @param \Drupal\vsite\Plugin\VsiteContextManagerInterface $vsite_context_manager
   *   Vsite context manager.
   */
  public function __construct(VsiteContextManagerInterface $vsite_context_manager) {
    $this->vsiteContextManager = $vsite_context_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('vsite.context_manager'));
  }

  /**
   * Checks whether a user can change a vsite ownership.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The account to check access for.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access(AccountInterface $account): AccessResultInterface {
    /** @var \Drupal\group\Entity\GroupInterface|null $active_vsite */
    $active_vsite = $this->vsiteContextManager->getActiveVsite();

    if (!$active_vsite) {
      return AccessResult::forbidden();
    }

    if ($active_vsite->hasPermission('manage cp users', $account) &&
      ($active_vsite->getMember($account) !== FALSE) &&
      $active_vsite->getOwnerId() === $account->id()) {
      return AccessResult::allowed();
    }

    return AccessResult::neutral();
  }

}
