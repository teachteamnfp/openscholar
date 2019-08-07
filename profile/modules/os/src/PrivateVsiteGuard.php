<?php

namespace Drupal\os;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\vsite\Plugin\VsiteContextManagerInterface;

/**
 * Guards requests made to a private vsite.
 */
class PrivateVsiteGuard implements AccessInterface {

  /**
   * Vsite context manager.
   *
   * @var \Drupal\vsite\Plugin\VsiteContextManagerInterface
   */
  protected $vsiteContextManager;

  /**
   * Creates a new PrivateVsiteGuard object.
   *
   * @param \Drupal\vsite\Plugin\VsiteContextManagerInterface $vsite_context_manager
   *   Vsite context manager.
   */
  public function __construct(VsiteContextManagerInterface $vsite_context_manager) {
    $this->vsiteContextManager = $vsite_context_manager;
  }

  /**
   * Checks whether a user has access to a private vsite request.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function access(AccountInterface $account): AccessResultInterface {
    /** @var \Drupal\group\Entity\GroupInterface|null $active_vsite */
    $active_vsite = $this->vsiteContextManager->getActiveVsite();

    if (!$active_vsite) {
      return AccessResult::allowed();
    }

    /** @var string $privacy_level */
    $privacy_level = $active_vsite->get('field_privacy_level')->first()->getValue()['value'];

    if ($privacy_level === 'private' && $account->isAnonymous()) {
      return AccessResult::forbidden();
    }

    return AccessResult::allowed();
  }

}
