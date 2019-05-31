<?php

namespace Drupal\Tests\cp_roles\ExistingSiteJavascript;

use Drupal\group\Entity\GroupRole;

/**
 * Vsite roles tests.
 *
 * @group functional-javascript
 * @group cp
 */
class CpRolesTest extends CpRolesExistingSiteJavascriptTestBase {

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
   * @covers ::cp_roles_form_group_role_add_form_alter
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   * @throws \Behat\Mink\Exception\ResponseTextException
   */
  public function testCreate(): void {
    $this->drupalLogin($this->groupAdmin);

    $this->visit("/{$this->group->get('path')->getValue()[0]['alias']}/cp/users/roles/add");
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
   * {@inheritdoc}
   */
  public function tearDown() {
    $vsite_context_manager = $this->container->get('vsite.context_manager');
    $vsite_context_manager->activateVsite($this->group);
    $group_role = GroupRole::load("personal-{$this->group->id()}_stooges");
    $group_role->delete();

    parent::tearDown();
  }

}
