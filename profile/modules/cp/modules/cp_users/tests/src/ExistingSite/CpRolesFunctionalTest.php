<?php

namespace Drupal\Tests\cp_users\ExistingSite;

/**
 * Custom roles test.
 *
 * @group functional
 * @group cp
 */
class CpRolesFunctionalTest extends CpUsersExistingSiteTestBase {

  /**
   * Tests roles listing when viewed from vsite.
   *
   * @covers \Drupal\cp_users\Controller\CpRolesListBuilder
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testListingVsite(): void {
    // Setup.
    $group_role = $this->createRoleForGroup($this->group, [
      'id' => 'test_role',
      'label' => 'Test Role',
    ]);

    $group_admin = $this->createUser();
    $this->addGroupAdmin($group_admin, $this->group);

    // Tests.
    $this->drupalLogin($group_admin);

    $this->visit("/{$this->group->get('path')->getValue()[0]['alias']}/cp/users/roles");

    $this->assertSession()->pageTextContains('Test Role');
    $this->assertSession()->linkByHrefExists("{$this->groupAlias}/cp/users/permissions/personal-{$this->group->id()}_test_role");
    $this->assertSession()->linkByHrefExists("{$this->groupAlias}/cp/users/roles/{$group_role->id()}/edit?destination={$this->groupAlias}/cp/users/roles");
    $this->assertSession()->linkByHrefExists("{$this->groupAlias}/cp/users/roles/{$group_role->id()}/delete?destination={$this->groupAlias}/cp/users/roles");

    $this->assertSession()->linkByHrefNotExists("{$this->groupAlias}/cp/users/permissions/personal-member");
    $this->assertSession()->linkByHrefNotExists("{$this->groupAlias}/cp/users/roles/personal-member/edit?destination={$this->groupAlias}/cp/users/roles");
    $this->assertSession()->linkByHrefNotExists("{$this->groupAlias}/cp/users/roles/personal-member/delete?destination={$this->groupAlias}/cp/users/roles");

    $this->assertSession()->linkByHrefNotExists("{$this->groupAlias}/cp/users/permissions/personal-administrator");
    $this->assertSession()->linkByHrefNotExists("{$this->groupAlias}/cp/users/roles/personal-administrator/edit?destination={$this->groupAlias}/cp/users/roles");
    $this->assertSession()->linkByHrefNotExists("{$this->groupAlias}/cp/users/roles/personal-administrator/delete?destination={$this->groupAlias}/cp/users/roles");

    $this->assertSession()->linkByHrefNotExists("{$this->groupAlias}/cp/users/permissions/personal-content_editor");
    $this->assertSession()->linkByHrefNotExists("{$this->groupAlias}/cp/users/roles/personal-content_editor/edit?destination={$this->groupAlias}/cp/users/roles");
    $this->assertSession()->linkByHrefNotExists("{$this->groupAlias}/cp/users/roles/personal-content_editor/delete?destination={$this->groupAlias}/cp/users/roles");
  }

  /**
   * Tests roles listing when viewed from outside vsite.
   *
   * @covers \Drupal\cp_users\Controller\CpRolesListBuilder
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testListingOutsideVsite(): void {
    // Setup.
    $site_admin = $this->createUser([
      'administer group',
      'manage default group roles',
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

  /**
   * Checks the accessibility of group roles as a vsite admin.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testCpRolesOperationsAccess(): void {
    // Setup.
    $group_role = $this->createRoleForGroup($this->group);

    $group_admin = $this->createUser();
    $this->addGroupAdmin($group_admin, $this->group);

    // Tests.
    $this->drupalLogin($group_admin);

    $this->visitViaVsite("cp/users/permissions/{$group_role->id()}", $this->group);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->fieldExists("{$group_role->id()}[access control panel]");
    $this->visitViaVsite("cp/users/roles/{$group_role->id()}/edit", $this->group);
    $this->assertSession()->statusCodeEquals(200);
    $this->visitViaVsite("cp/users/roles/{$group_role->id()}/delete", $this->group);
    $this->assertSession()->statusCodeEquals(200);

    $this->visitViaVsite('cp/users/permissions/personal-member', $this->group);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Error');
    $this->visitViaVsite('cp/users/roles/personal-member/edit', $this->group);
    $this->assertSession()->statusCodeEquals(403);
    $this->visitViaVsite('cp/users/roles/personal-member/delete', $this->group);
    $this->assertSession()->statusCodeEquals(403);

    $this->visitViaVsite('cp/users/permissions/personal-administrator', $this->group);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Error');
    $this->visitViaVsite('cp/users/roles/personal-administrator/edit', $this->group);
    $this->assertSession()->statusCodeEquals(403);
    $this->visitViaVsite('cp/users/roles/personal-administrator/delete', $this->group);
    $this->assertSession()->statusCodeEquals(403);

    $this->visitViaVsite('cp/users/permissions/personal-content_editor', $this->group);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Error');
    $this->visitViaVsite('cp/users/roles/personal-content_editor/edit', $this->group);
    $this->assertSession()->statusCodeEquals(403);
    $this->visitViaVsite('cp/users/roles/personal-content_editor/delete', $this->group);
    $this->assertSession()->statusCodeEquals(403);
  }

}
