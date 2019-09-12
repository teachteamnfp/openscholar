<?php

namespace Drupal\Tests\os\ExistingSite;

use Drupal\Core\Access\AccessResultAllowed;
use Drupal\Core\Access\AccessResultForbidden;
use Drupal\Tests\openscholar\ExistingSite\OsExistingSiteTestBase;

/**
 * PrivateVsiteGuard test.
 *
 * @coversDefaultClass \Drupal\os\PrivateVsiteGuard
 *
 * @group kernel
 * @group os
 */
class PrivateVsiteGuardTest extends OsExistingSiteTestBase {

  /**
   * @covers ::access
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function test(): void {
    /** @var \Drupal\Core\Session\AccountProxyInterface $current_user */
    $current_user = $this->container->get('current_user');
    /** @var \Drupal\vsite\Plugin\VsiteContextManagerInterface $vsite_context_manager */
    $vsite_context_manager = $this->container->get('vsite.context_manager');
    /** @var \Drupal\os\PrivateVsiteGuard $private_vsite_guard */
    $private_vsite_guard = $this->container->get('os.private_vsite_guard');
    /** @var \Drupal\group\Entity\GroupInterface $private_vsite */
    $private_vsite = $this->createPrivateGroup();

    // Tests.
    $this->assertInstanceOf(AccessResultAllowed::class, $private_vsite_guard->access($current_user));

    $vsite_context_manager->activateVsite($this->group);
    $this->assertInstanceOf(AccessResultAllowed::class, $private_vsite_guard->access($current_user));

    $vsite_context_manager->activateVsite($private_vsite);
    $this->assertInstanceOf(AccessResultForbidden::class, $private_vsite_guard->access($current_user));
  }

}
