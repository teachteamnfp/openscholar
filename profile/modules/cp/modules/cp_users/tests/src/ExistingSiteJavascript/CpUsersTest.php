<?php

namespace Drupal\Tests\cp_users\ExistingSiteJavascript;

use Drupal\group\Entity\GroupRole;

/**
 * Vsite roles tests.
 *
 * @group functional-javascript
 * @group cp
 */
class CpUsersTest extends CpUsersExistingSiteJavascriptTestBase {

  /**
   * Purpose.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $groupAdmin;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->groupAdmin = $this->createUser();
    $this->addGroupAdmin($this->groupAdmin, $this->group);
  }

  /**
   * Tests role creation from UI.
   *
   * @covers ::cp_users_form_group_role_add_form_alter
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   * @throws \Behat\Mink\Exception\ResponseTextException
   */
  public function testCreate(): void {
    $this->drupalLogin($this->groupAdmin);

    $this->visit("/{$this->group->get('path')->getValue()[0]['alias']}/cp/users/roles/add/{$this->group->getGroupType()->id()}");
    $this->getSession()->getPage()->fillField('Name', 'Stooges');
    $this->assertSession()->waitForElementVisible('css', '.machine-name-value');
    $this->assertSession()->pageTextContains("personal-{$this->group->id()}_stooges");

    $this->getSession()->getPage()->pressButton('Save group role');

    $this->assertContains("{$this->group->get('path')->getValue()[0]['alias']}/cp/users/roles", $this->getSession()->getCurrentUrl());
    $this->assertSession()->pageTextContains('Stooges');
  }

  /**
   * Tests custom role edit via UI.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testEdit(): void {
    $group_role = $this->createRoleForGroup($this->group, [
      'id' => 'stooges',
      'label' => 'The Stooges',
    ]);

    $this->drupalLogin($this->groupAdmin);

    $this->visit("/{$this->group->get('path')->getValue()[0]['alias']}/cp/users/roles");
    $group_role_edit_link = $this->getSession()->getPage()->find('css', "[href='{$this->group->get('path')->getValue()[0]['alias']}/cp/users/roles/{$group_role->id()}/edit?destination={$this->group->get('path')->getValue()[0]['alias']}/cp/users/roles']");
    $group_role_edit_link->click();

    $this->getSession()->getPage()->fillField('Name', 'The Stooges Funhouse');
    $this->getSession()->getPage()->pressButton('Save group role');

    $this->assertSession()->pageTextContains('The Stooges Funhouse');
  }

  /**
   * Tests custom role delete via UI.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testDelete(): void {
    $group_role = $this->createRoleForGroup($this->group, [
      'id' => 'stooges',
      'label' => 'The Stooges',
    ]);

    $this->drupalLogin($this->groupAdmin);

    $this->visit("/{$this->group->get('path')->getValue()[0]['alias']}/cp/users/roles");

    $group_role_delete_link = $this->getSession()->getPage()->find('css', "[href='{$this->group->get('path')->getValue()[0]['alias']}/cp/users/roles/{$group_role->id()}/delete?destination={$this->group->get('path')->getValue()[0]['alias']}/cp/users/roles']");
    $group_role_delete_link->click();

    $this->getSession()->getPage()->pressButton('Delete');

    $this->assertSession()->elementNotExists('css', '[data-drupal-selector="edit-entities-personal-stooges"]');
  }

  /**
   * {@inheritdoc}
   */
  public function tearDown() {
    $vsite_context_manager = $this->container->get('vsite.context_manager');
    $vsite_context_manager->activateVsite($this->group);
    $group_role = GroupRole::load("personal-{$this->group->id()}_stooges");
    if ($group_role) {
      $group_role->delete();
    }

    parent::tearDown();
  }

}
