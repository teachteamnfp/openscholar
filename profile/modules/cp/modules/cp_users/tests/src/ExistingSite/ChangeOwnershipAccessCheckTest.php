<?php

namespace Drupal\Tests\cp_users\ExistingSite;

use Drupal\Core\Access\AccessResultAllowed;
use Drupal\Core\Access\AccessResultForbidden;
use Drupal\Core\Access\AccessResultNeutral;
use Drupal\Tests\openscholar\ExistingSite\OsExistingSiteTestBase;

/**
 * Test ChangeOwnershipAccessCheck.
 *
 * @group kernel
 * @group cp
 *
 * @coversDefaultClass \Drupal\cp_users\Access\ChangeOwnershipAccessCheck
 */
class ChangeOwnershipAccessCheckTest extends OsExistingSiteTestBase {

  /**
   * @covers ::access
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function test(): void {
    /** @var \Drupal\cp_users\Access\ChangeOwnershipAccessCheck $change_ownership_access_check_service */
    $change_ownership_access_check_service = $this->container->get('cp_users.change_ownership_access_check');
    /** @var \Drupal\vsite\Plugin\VsiteContextManagerInterface $vsite_context_manager */
    $vsite_context_manager = $this->container->get('vsite.context_manager');

    // Negative tests.
    $account = $this->createUser();
    $this->assertInstanceOf(AccessResultForbidden::class, $change_ownership_access_check_service->access($account));

    $this->addGroupAdmin($account, $this->group);
    $vsite_context_manager->activateVsite($this->group);

    $this->assertInstanceOf(AccessResultNeutral::class, $change_ownership_access_check_service->access($account));

    // Positive tests.
    $this->group->setOwner($account)->save();

    $this->assertInstanceOf(AccessResultAllowed::class, $change_ownership_access_check_service->access($account));
  }

}
