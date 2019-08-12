<?php

namespace Drupal\Tests\cp_users\ExistingSite;

use Drupal\group\Entity\GroupRole;
use Drupal\group\Entity\GroupType;

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
   * @covers ::getDefaultGroupRoles
   */
  public function testGetNonEditableGroupRoles(): void {
    /** @var \Drupal\cp_users\CpRolesHelperInterface $cp_roles_helper */
    $cp_roles_helper = $this->container->get('cp_users.cp_roles_helper');
    $roles = $cp_roles_helper->getDefaultGroupRoles($this->group);

    $this->assertCount(3, $roles);
    $this->assertContains('personal-administrator', $roles);
    $this->assertContains('personal-member', $roles);
    $this->assertContains('personal-content_editor', $roles);
  }

  /**
   * @covers ::isDefaultGroupRole
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testIsDefaultGroupRole(): void {
    /** @var \Drupal\cp_users\CpRolesHelperInterface $cp_roles_helper */
    $cp_roles_helper = $this->container->get('cp_users.cp_roles_helper');

    $default_role = GroupRole::load('personal-administrator');
    $this->assertTrue($cp_roles_helper->isDefaultGroupRole($default_role));

    $custom_role = $this->createGroupRole();
    $this->assertFalse($cp_roles_helper->isDefaultGroupRole($custom_role));
  }

  /**
   * @covers ::getRestrictedPermissions
   */
  public function testGetRestrictedPermissions(): void {
    /** @var \Drupal\cp_users\CpRolesHelperInterface $cp_roles_helper */
    $cp_roles_helper = $this->container->get('cp_users.cp_roles_helper');
    $group_type = GroupType::load('personal');

    $restricted_permissions = $cp_roles_helper->getRestrictedPermissions($group_type);

    // It is not possible to check for all group content plugins, therefore,
    // only group_node:blog is checked here.
    $this->assertContains('view group_node:blog content', $restricted_permissions);
    $this->assertContains('create group_node:blog content', $restricted_permissions);
    $this->assertContains('update own group_node:blog content', $restricted_permissions);
    $this->assertContains('update any group_node:blog content', $restricted_permissions);
    $this->assertContains('delete own group_node:blog content', $restricted_permissions);
    $this->assertContains('delete any group_node:blog content', $restricted_permissions);

    $this->assertNotContains('view group_node:blog entity', $restricted_permissions);
    $this->assertNotContains('create group_node:blog entity', $restricted_permissions);
    $this->assertNotContains('update own group_node:blog entity', $restricted_permissions);
    $this->assertNotContains('update any group_node:blog entity', $restricted_permissions);
    $this->assertNotContains('delete own group_node:blog entity', $restricted_permissions);
    $this->assertNotContains('delete any group_node:blog entity', $restricted_permissions);
  }

}
