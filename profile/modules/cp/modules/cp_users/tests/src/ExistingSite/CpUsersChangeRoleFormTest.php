<?php

namespace Drupal\Tests\cp_users\ExistingSite;

use Drupal\group\Entity\GroupRole;

/**
 * ChangeRoleForm test.
 *
 * @group functional
 * @group cp
 * @coversDefaultClass \Drupal\cp_users\Form\ChangeRoleForm
 */
class CpUsersChangeRoleFormTest extends CpUsersExistingSiteTestBase {

  /**
   * Cp Roles helper service.
   *
   * @var \Drupal\cp_users\CpRolesHelperInterface
   */
  protected $cpRolesHelper;

  /**
   * Group member.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $member;

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function setUp() {
    parent::setUp();

    $group_admin = $this->createUser();
    $this->addGroupAdmin($group_admin, $this->group);
    $this->createRoleForGroup($this->group, [
      'id' => 'cprolechange',
    ]);
    $this->member = $this->createUser();
    $this->group->addMember($this->member);

    $this->drupalLogin($group_admin);

    $this->cpRolesHelper = $this->container->get('cp_users.cp_roles_helper');

  }

  /**
   * Tests change role functionality.
   *
   * @throws \Behat\Mink\Exception\ResponseTextException
   */
  public function test(): void {

    $this->visit("/{$this->group->get('path')->getValue()[0]['alias']}/cp/users/change-role/{$this->member->id()}");

    $this->assertSession()->statusCodeEquals(200);

    // Test Non Configurable roles do not appear.
    $non_configurable_roles = $this->cpRolesHelper->getNonConfigurableGroupRoles($this->group);
    foreach ($non_configurable_roles as $role) {
      $this->assertSession()->elementNotExists('css', '#edit-roles-' . $role);
    }

    $this->assertTrue($this->getSession()->getPage()->find('css', '[value="personal-member"]')->isChecked());

    $this->drupalPostForm(NULL, [
      'roles' => "personal-{$this->group->id()}_cprolechange",
    ], 'Save');

    /** @var \Drupal\group\GroupMembership $group_membership */
    $group_membership = $this->group->getMember($this->member);
    $updated_roles = $group_membership->getRoles();
    $this->assertInstanceOf(GroupRole::class, $updated_roles["personal-{$this->group->id()}_cprolechange"]);
  }

}
