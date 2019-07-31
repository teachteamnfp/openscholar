<?php

namespace Drupal\Tests\cp_users\ExistingSite;

/**
 * Custom roles test.
 *
 * @group functional
 * @group cp
 */
class CpUsersFunctionalTest extends CpUsersExistingSiteTestBase {

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
    $this->assertSession()->responseContains("{$this->group->get('path')->getValue()[0]['alias']}/cp/users/permissions/personal-{$this->group->id()}_test_role");
    file_put_contents('public://page-name.html', $this->getCurrentPageContent());
    $this->assertSession()->linkByHrefExists("{$this->groupAlias}/cp/users/roles/{$group_role->id()}/edit/{$this->group->getGroupType()->id()}?destination={$this->groupAlias}/cp/users/roles");
    $this->assertSession()->linkByHrefExists("{$this->groupAlias}/cp/users/permissions/personal-member");
    $this->assertSession()->linkByHrefNotExists("{$this->groupAlias}/cp/users/roles/personal-member/edit/{$this->group->getGroupType()->id()}?destination={$this->groupAlias}/cp/users/roles");
    $this->assertSession()->linkByHrefExists("{$this->groupAlias}/cp/users/permissions/personal-administrator");
    $this->assertSession()->linkByHrefNotExists("{$this->groupAlias}/cp/users/roles/personal-administrator/edit/{$this->group->getGroupType()->id()}?destination={$this->groupAlias}/cp/users/roles");
    $this->assertSession()->linkByHrefExists("{$this->groupAlias}/cp/users/permissions/personal-content_editor");
    $this->assertSession()->linkByHrefNotExists("{$this->groupAlias}/cp/users/roles/personal-content_editor/edit/{$this->group->getGroupType()->id()}?destination={$this->groupAlias}/cp/users/roles");
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
