<?php

namespace Drupal\Tests\cp_menu\ExistingSiteJavaScript;

use Drupal\Tests\openscholar\ExistingSiteJavascript\OsExistingSiteJavascriptTestBase;

/**
 * Class CpMenuLinksTest.
 *
 * @group functional-javascript
 * @group cp-menu
 *
 * @package Drupal\Tests\cp_menu\ExistingSiteJavaScript
 */
class CpMenuLinksTest extends OsExistingSiteJavascriptTestBase {
  /**
   * Test group.
   *
   * @var \Drupal\group\Entity\GroupInterface
   */
  protected $group;

  /**
   * Group Id.
   *
   * @var string
   */
  protected $id;

  /**
   * Group administrator.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $groupAdmin;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->group = $this->createGroup([
      'path' => [
        'alias' => '/test-menu',
      ],
    ]);
    $this->groupAdmin = $this->createUser();
    $this->addGroupAdmin($this->groupAdmin, $this->group);
    $this->id = $this->group->id();
    // Test as groupAdmin.
    $this->drupalLogin($this->groupAdmin);
  }

  /**
   * Tests Menu Link edit.
   */
  public function testMenuLinkEdit(): void {

    $this->visit('/test-menu/cp/build/menu');
    $session = $this->assertSession();
    $page = $this->getCurrentPage();

    $link = $page->find('css', '.Calendar #edit_menu_link');
    $link->click();
    $session->waitForElementVisible('css', '.cp-menu-link-edit');
    $edit = [
      'title' => 'Test Link',
      'tooltip' => 'Test Link to test this.',
    ];
    $this->submitForm($edit, 'Save');
    $session->waitForText('Test Link');
  }

  /**
   * Tests Menu link deletion.
   */
  public function testMenuLinkDelete(): void {
    $this->visit('/test-menu/cp/build/menu');
    $session = $this->assertSession();
    $page = $this->getCurrentPage();
    $session->elementExists('css', '#cp-build-menu-table .Calendar');
    $link = $page->find('css', '.Calendar #delete_menu_link');
    $link->click();
    $session->waitForElementVisible('css', '.cp-delete-menu-link');
    $this->submitForm([], 'Confirm');
    $session->waitForElementVisible('css', '#cp-build-menu-table');
    $session->elementNotExists('css', '#cp-build-menu-table .Calendar');
  }

}
