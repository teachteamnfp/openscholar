<?php

namespace Drupal\Tests\cp_users\ExistingSite;

/**
 * CpUsersEditableTest.
 *
 * @coversDefaultClass \Drupal\cp_users\CpUsersEditable
 * @group kernel
 * @group cp
 */
class CpUsersEditableTest extends CpUsersExistingSiteTestBase {

  /**
   * @covers ::getNonConfigurableGroupRoles
   */
  public function testGetNonConfigurableGroupRoles(): void {
    /** @var \Drupal\cp_users\CpUsersEditableInterface $cp_users_editable */
    $cp_users_editable = $this->container->get('cp_users.editable');
    $roles = $cp_users_editable->getNonConfigurableGroupRoles($this->group);

    $this->assertCount(2, $roles);
    $this->assertContains('personal-anonymous', $roles);
    $this->assertContains('personal-outsider', $roles);
  }

  /**
   * @covers ::getNonEditableGroupRoles
   */
  public function testGetNonEditableGroupRoles(): void {
    /** @var \Drupal\cp_users\CpUsersEditableInterface $cp_users_editable */
    $cp_users_editable = $this->container->get('cp_users.editable');
    $roles = $cp_users_editable->getNonEditableGroupRoles($this->group);

    $this->assertCount(3, $roles);
    $this->assertContains('personal-administrator', $roles);
    $this->assertContains('personal-member', $roles);
    $this->assertContains('personal-content_editor', $roles);
  }

}
