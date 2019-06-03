<?php

namespace Drupal\os;

use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides the interface for entity CRUD global path access check helpers.
 */
interface AccessHelperInterface {

  /**
   * Controls entity create access.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user trying to create the entity.
   * @param string $plugin_id
   *   Group plugin id of the entity which would be created.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function checkCreateAccess(AccountInterface $account, string $plugin_id): AccessResultInterface;

  /**
   * Controls entity operation access.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to check access to.
   * @param string $operation
   *   The operation to be performed on the entity.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user trying to access the entity.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function checkAccess(EntityInterface $entity, string $operation, AccountInterface $account): AccessResultInterface;

}
