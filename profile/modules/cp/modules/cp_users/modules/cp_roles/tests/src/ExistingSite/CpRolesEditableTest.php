<?php

namespace Drupal\Tests\cp_roles\ExistingSite;

/**
 * CpRolesEditableTest.
 *
 * @coversDefaultClass \Drupal\cp_roles\CpRolesEditable
 * @group kernel
 * @group cp
 */
class CpRolesEditableTest extends CpRolesExistingSiteTestBase {

  /**
   * @covers ::getNonConfigurableGroupRoles
   */
  public function testGetNonConfigurableGroupRoles(): void {
    /** @var \Drupal\cp_roles\CpRolesEditableInterface $cp_roles_editable */
    $cp_roles_editable = $this->container->get('cp_roles.editable');
    $roles = $cp_roles_editable->getNonConfigurableGroupRoles($this->group);

    $this->assertCount(2, $roles);
    $this->assertContains('personal-anonymous', $roles);
    $this->assertContains('personal-outsider', $roles);
  }

  /**
   * @covers ::getDefaultGroupRoles
   */
  public function testGetNonEditableGroupRoles(): void {
    /** @var \Drupal\cp_roles\CpRolesEditableInterface $cp_roles_editable */
    $cp_roles_editable = $this->container->get('cp_roles.editable');
    $roles = $cp_roles_editable->getDefaultGroupRoles($this->group);

    $this->assertCount(3, $roles);
    $this->assertContains('personal-administrator', $roles);
    $this->assertContains('personal-member', $roles);
    $this->assertContains('personal-content_editor', $roles);
  }

}
