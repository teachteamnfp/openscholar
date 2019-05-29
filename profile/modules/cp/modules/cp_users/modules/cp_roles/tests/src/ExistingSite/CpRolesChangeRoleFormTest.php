<?php

namespace Drupal\Tests\cp_roles\ExistingSite;

use Drupal\group\Entity\GroupRole;

/**
 * ChangeRoleForm test.
 *
 * @group functional
 * @group cp
 * @coversDefaultClass \Drupal\cp_roles\Form\ChangeRoleForm
 */
class CpRolesChangeRoleFormTest extends CpRolesExistingSiteTestBase {

  /**
   * Tests change role functionality.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function test(): void {
    $group_admin = $this->createUser();
    $this->addGroupAdmin($group_admin, $this->group);
    $this->createRoleForGroup($this->group, [
      'id' => 'cprolechange',
    ]);
    $member = $this->createUser();
    $this->group->addMember($member);

    $this->drupalLogin($group_admin);

    $this->visit("/{$this->group->get('path')->getValue()[0]['alias']}/cp/users/change-role/{$member->id()}");

    $this->assertSession()->statusCodeEquals(200);

    $this->assertTrue($this->getSession()->getPage()->find('css', '[value="personal-member"]')->isChecked());

    $this->drupalPostForm(NULL, [
      'roles' => "personal-{$this->group->id()}-cprolechange",
    ], 'Save');

    /** @var \Drupal\group\GroupMembership $group_membership */
    $group_membership = $this->group->getMember($member);
    $updated_roles = $group_membership->getRoles();
    $this->assertInstanceOf(GroupRole::class, $updated_roles["personal-{$this->group->id()}-cprolechange"]);
  }

}
