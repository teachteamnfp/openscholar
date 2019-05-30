<?php

namespace Drupal\Tests\cp_roles\ExistingSite;

/**
 * Custom roles test.
 *
 * @group functional
 * @group cp
 */
class CpRolesFunctionalTest extends CpRolesExistingSiteTestBase {

  /**
   * Tests roles listing when viewed from vsite.
   *
   * @covers \Drupal\cp_roles\Controller\CpRoleListBuilder
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testListingVsite(): void {
    // Setup.
    $this->createRoleForGroup($this->group, [
      'id' => 'test_role',
      'label' => 'Test Role',
    ]);

    $group_admin = $this->createUser();
    $this->addGroupAdmin($group_admin, $this->group);

    // Tests.
    $this->drupalLogin($group_admin);

    $this->visit("/{$this->group->get('path')->getValue()[0]['alias']}/cp/users/roles");

    $this->assertSession()->pageTextContains('Test Role');
    $this->assertSession()->responseContains("{$this->group->get('path')->getValue()[0]['alias']}/cp/users/permissions/personal-{$this->group->id()}-test_role");
  }

  /**
   * Tests roles listing when viewed from outside vsite.
   *
   * @covers \Drupal\cp_roles\Controller\CpRoleListBuilder
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testListingOutsideVsite(): void {
    // Setup.
    $site_admin = $this->createUser([
      'administer group',
    ]);
    $this->createRoleForGroup($this->group, [
      'id' => 'outside_vsite',
      'label' => 'Outside Vsite',
    ]);

    // Tests.
    $this->drupalLogin($site_admin);

    $this->visit('/admin/group/types/manage/personal/roles');

    $this->assertSession()->pageTextNotContains('Outside Vsite');
    $this->assertSession()->responseNotContains("{$this->group->get('path')->getValue()[0]['alias']}/cp/users/permissions/personal-{$this->group->id()}-outside_vsite");
    $this->assertSession()->responseContains('/admin/group/types/manage/personal/roles/personal-administrator/permissions');
  }

}
