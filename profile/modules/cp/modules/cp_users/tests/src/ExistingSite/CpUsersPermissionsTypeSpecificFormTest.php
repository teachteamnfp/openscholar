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
   * Tests the form customizations.
   *
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function test(): void {
    // Setup.
    $group_admin = $this->createUser();
    $this->addGroupAdmin($group_admin, $this->group);
    $this->createRoleForGroup($this->group, [
      'id' => 'test_role',
      'label' => 'Test Role',
    ]);

    // Tests.
    $this->drupalLogin($group_admin);

    $this->visit("{$this->groupAlias}/cp/users/permissions");
    $this->assertSession()->pageTextNotContains('Anonymous');
    $this->assertSession()->pageTextNotContains('Outsider');
    $this->assertSession()->pageTextContains('Basic member');
    $this->assertSession()->pageTextContains('Administrator');
    $this->assertSession()->pageTextContains('Content editor');
    $this->assertSession()->pageTextContains('Test Role');
  }

}
