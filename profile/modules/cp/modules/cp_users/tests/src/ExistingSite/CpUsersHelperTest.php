<?php

namespace Drupal\Tests\cp_users\ExistingSite;

/**
 * CpUsersHelper test.
 *
 * @coversDefaultClass \Drupal\cp_users\CpUsersHelper
 * @group kernel
 * @group cp
 */
class CpUsersHelperTest extends CpUsersExistingSiteTestBase {

  /**
   * @covers ::isVsiteOwner
   */
  public function test(): void {
    $vsite_owner = $this->createUser();
    $this->group->setOwner($vsite_owner)->save();
    $non_vsite_owner = $this->createUser();
    /** @var \Drupal\cp_users\CpUsersHelperInterface $cp_users_helper */
    $cp_users_helper = $this->container->get('cp_users.cp_users_helper');

    $this->assertTrue($cp_users_helper->isVsiteOwner($this->group, $vsite_owner));
    $this->assertFalse($cp_users_helper->isVsiteOwner($this->group, $non_vsite_owner));
  }

}
