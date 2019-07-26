<?php

namespace Drupal\Tests\cp_users\ExistingSite;

/**
 * CpUsersHelperTest.
 *
 * @coversDefaultClass \Drupal\cp_users\CpRolesHelper
 * @group kernel
 * @group cp
 */
class CpRolesHelperTest extends CpUsersExistingSiteTestBase {

  /**
   * @covers ::getNonConfigurableGroupRoles
   */
  public function testGetNonConfigurableGroupRoles(): void {
    /** @var \Drupal\cp_users\CpRolesHelperInterface $cp_roles_helper */
    $cp_roles_helper = $this->container->get('cp_users.cp_roles_helper');
    $roles = $cp_roles_helper->getNonConfigurableGroupRoles($this->group);

    $this->assertCount(2, $roles);
    $this->assertContains('personal-anonymous', $roles);
    $this->assertContains('personal-outsider', $roles);
  }

  /**
   * @covers ::getNonEditableGroupRoles
   */
  public function testGetNonEditableGroupRoles(): void {
    /** @var \Drupal\cp_users\CpRolesHelperInterface $cp_roles_helper */
    $cp_roles_helper = $this->container->get('cp_users.cp_roles_helper');
    $roles = $cp_roles_helper->getNonEditableGroupRoles($this->group);

    $this->assertCount(3, $roles);
    $this->assertContains('personal-administrator', $roles);
    $this->assertContains('personal-member', $roles);
    $this->assertContains('personal-content_editor', $roles);
  }

}
