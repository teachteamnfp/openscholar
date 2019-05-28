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
    $this->assertSession()->pageTextContains("personal-{$this->group->id()}-stooges");

    $this->getSession()->getPage()->pressButton('Save group role');

    $this->assertContains("{$this->group->get('path')->getValue()[0]['alias']}/cp/users/roles", $this->getSession()->getCurrentUrl());
    $this->assertSession()->pageTextContains('Stooges');
  }

  /**
   * {@inheritdoc}
   */
  public function tearDown() {
    $vsite_context_manager = $this->container->get('vsite.context_manager');
    $vsite_context_manager->activateVsite($this->group);
    $group_role = GroupRole::load("personal-{$this->group->id()}-stooges");
    $group_role->delete();

    parent::tearDown();
  }

}
