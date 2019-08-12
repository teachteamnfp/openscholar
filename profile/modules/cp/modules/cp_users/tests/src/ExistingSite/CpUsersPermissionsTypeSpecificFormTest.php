<?php

namespace Drupal\Tests\cp_users\ExistingSite;

/**
 * CpUsersPermissionsTypeSpecificFormTest.
 *
 * @coversDefaultClass \Drupal\cp_users\Form\CpUsersPermissionsTypeSpecificForm
 * @group functional
 * @group cp
 */
class CpUsersPermissionsTypeSpecificFormTest extends CpUsersExistingSiteTestBase {

  /**
   * Tests the form customizations - as vsite admin.
   *
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testVsiteAdmin(): void {
    // Setup.
    $group_admin = $this->createUser();
    $this->addGroupAdmin($group_admin, $this->group);
    $group_role = $this->createRoleForGroup($this->group, [
      'id' => 'test_role',
      'label' => 'Test Role',
    ]);

    // Tests.
    $this->drupalLogin($group_admin);

    $this->visitViaVsite('cp/users/permissions', $this->group);

    $this->assertSession()->pageTextNotContains('Anonymous');
    $this->assertSession()->pageTextNotContains('Outsider');

    // Make sure role permission edits are restricted.
    $this->assertSession()->pageTextContains('Basic member');
    $this->assertTrue($this->getSession()->getPage()->findField('personal-member[access control panel]')->hasAttribute('disabled'));

    $this->assertSession()->pageTextContains('Administrator');
    $this->assertTrue($this->getSession()->getPage()->findField('personal-administrator[access control panel]')->hasAttribute('disabled'));

    $this->assertSession()->pageTextContains('Content editor');
    $this->assertTrue($this->getSession()->getPage()->findField('personal-content_editor[access control panel]')->hasAttribute('disabled'));

    $this->assertSession()->pageTextContains('Test Role');
    $this->assertFalse($this->getSession()->getPage()->findField("{$group_role->id()}[access control panel]")->hasAttribute('disabled'));

    // Make sure that group content (relationship) permissions are not
    // displayed.
    $this->assertNull($this->getSession()->getPage()->findField('personal-member[view group_node:blog content]'));
    $this->assertNull($this->getSession()->getPage()->findField('personal-member[create group_node:blog content]'));
    $this->assertNull($this->getSession()->getPage()->findField('personal-member[update own group_node:blog content]'));
    $this->assertNull($this->getSession()->getPage()->findField('personal-member[update any group_node:blog content]'));
    $this->assertNull($this->getSession()->getPage()->findField('personal-member[delete own group_node:blog content]'));
    $this->assertNull($this->getSession()->getPage()->findField('personal-member[delete any group_node:blog content]'));

    $this->assertNull($this->getSession()->getPage()->findField('personal-administrator[view group_node:blog content]'));
    $this->assertNull($this->getSession()->getPage()->findField('personal-administrator[create group_node:blog content]'));
    $this->assertNull($this->getSession()->getPage()->findField('personal-administrator[update own group_node:blog content]'));
    $this->assertNull($this->getSession()->getPage()->findField('personal-administrator[update any group_node:blog content]'));
    $this->assertNull($this->getSession()->getPage()->findField('personal-administrator[delete own group_node:blog content]'));
    $this->assertNull($this->getSession()->getPage()->findField('personal-administrator[delete any group_node:blog content]'));

    $this->assertNull($this->getSession()->getPage()->findField('personal-content_editor[view group_node:blog content]'));
    $this->assertNull($this->getSession()->getPage()->findField('personal-content_editor[create group_node:blog content]'));
    $this->assertNull($this->getSession()->getPage()->findField('personal-content_editor[update own group_node:blog content]'));
    $this->assertNull($this->getSession()->getPage()->findField('personal-content_editor[update any group_node:blog content]'));
    $this->assertNull($this->getSession()->getPage()->findField('personal-content_editor[delete own group_node:blog content]'));
    $this->assertNull($this->getSession()->getPage()->findField('personal-content_editor[delete any group_node:blog content]'));
  }

  /**
   * Tests form customizations - as normal admin.
   *
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testWithoutVsiteAdmin(): void {
    // Setup.
    $group_admin = $this->createUser([
      'manage default group roles',
    ]);
    $this->addGroupAdmin($group_admin, $this->group);
    $group_role = $this->createRoleForGroup($this->group, [
      'id' => 'test_role',
      'label' => 'Test Role',
    ]);

    // Tests.
    $this->drupalLogin($group_admin);

    $this->visitViaVsite('cp/users/permissions', $this->group);

    $this->assertSession()->pageTextNotContains('Anonymous');
    $this->assertSession()->pageTextNotContains('Outsider');

    // Make sure role permission edits are restricted.
    $this->assertSession()->pageTextContains('Basic member');
    $this->assertTrue($this->getSession()->getPage()->findField('personal-member[access control panel]')->hasAttribute('disabled'));

    $this->assertSession()->pageTextContains('Administrator');
    $this->assertTrue($this->getSession()->getPage()->findField('personal-administrator[access control panel]')->hasAttribute('disabled'));

    $this->assertSession()->pageTextContains('Content editor');
    $this->assertTrue($this->getSession()->getPage()->findField('personal-content_editor[access control panel]')->hasAttribute('disabled'));

    $this->assertSession()->pageTextContains('Test Role');
    $this->assertFalse($this->getSession()->getPage()->findField("{$group_role->id()}[access control panel]")->hasAttribute('disabled'));

    // Make sure that group content (relationship) permissions are not
    // displayed.
    $this->assertNull($this->getSession()->getPage()->findField('personal-member[view group_node:blog content]'));
    $this->assertNull($this->getSession()->getPage()->findField('personal-member[create group_node:blog content]'));
    $this->assertNull($this->getSession()->getPage()->findField('personal-member[update own group_node:blog content]'));
    $this->assertNull($this->getSession()->getPage()->findField('personal-member[update any group_node:blog content]'));
    $this->assertNull($this->getSession()->getPage()->findField('personal-member[delete own group_node:blog content]'));
    $this->assertNull($this->getSession()->getPage()->findField('personal-member[delete any group_node:blog content]'));

    $this->assertNull($this->getSession()->getPage()->findField('personal-administrator[view group_node:blog content]'));
    $this->assertNull($this->getSession()->getPage()->findField('personal-administrator[create group_node:blog content]'));
    $this->assertNull($this->getSession()->getPage()->findField('personal-administrator[update own group_node:blog content]'));
    $this->assertNull($this->getSession()->getPage()->findField('personal-administrator[update any group_node:blog content]'));
    $this->assertNull($this->getSession()->getPage()->findField('personal-administrator[delete own group_node:blog content]'));
    $this->assertNull($this->getSession()->getPage()->findField('personal-administrator[delete any group_node:blog content]'));

    $this->assertNull($this->getSession()->getPage()->findField('personal-content_editor[view group_node:blog content]'));
    $this->assertNull($this->getSession()->getPage()->findField('personal-content_editor[create group_node:blog content]'));
    $this->assertNull($this->getSession()->getPage()->findField('personal-content_editor[update own group_node:blog content]'));
    $this->assertNull($this->getSession()->getPage()->findField('personal-content_editor[update any group_node:blog content]'));
    $this->assertNull($this->getSession()->getPage()->findField('personal-content_editor[delete own group_node:blog content]'));
    $this->assertNull($this->getSession()->getPage()->findField('personal-content_editor[delete any group_node:blog content]'));
  }

}
