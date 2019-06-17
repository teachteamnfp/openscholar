<?php

namespace Drupal\Tests\cp_menu\ExistingSiteJavaScript;

use Drupal\Tests\openscholar\ExistingSiteJavascript\OsExistingSiteJavascriptTestBase;

/**
 * Class CpMenuLinkAddTest.
 *
 * @group functional-javascript
 * @group cp-menu
 *
 * @package Drupal\Tests\cp_menu\ExistingSiteJavaScript
 */
class CpMenuLinkAddTest extends OsExistingSiteJavascriptTestBase {
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
   * Tests Url type link creation.
   */
  public function testUrlLinkAdd(): void {

    $this->visit('/test-menu/cp/build/menu');
    $session = $this->assertSession();
    $page = $this->getCurrentPage();

    // Test adding internal link.
    $link = $page->find('css', '#add_new_link');
    $link->click();
    $session->waitForElementVisible('css', '.cp-menu-link-add-form');
    $edit = [
      'link_type' => 'url',
    ];
    $this->submitForm($edit, 'Continue');
    $session->assertWaitOnAjaxRequest();
    $edit = [
      'title' => 'Test Calendar Link',
      'url' => '/calendar',
    ];
    $this->submitForm($edit, 'Finish');
    $session->assertWaitOnAjaxRequest();
    $session->linkExists('Test Calendar Link');

    // Test adding external link.
    $link = $page->find('css', '#add_new_link');
    $link->click();
    $session->waitForElementVisible('css', '.cp-menu-link-add-form');
    $edit = [
      'link_type' => 'url',
    ];
    $this->submitForm($edit, 'Continue');
    $session->assertWaitOnAjaxRequest();
    $edit = [
      'title' => 'Test External Link',
      'url' => 'https://scholar.harvard.edu',
    ];
    $this->submitForm($edit, 'Finish');
    $session->assertWaitOnAjaxRequest();
    $session->linkExists('Test External Link');

    // Test invalid url for negative case.
    $link = $page->find('css', '#add_new_link');
    $link->click();
    $session->waitForElementVisible('css', '.cp-menu-link-add-form');
    $edit = [
      'link_type' => 'url',
    ];
    $this->submitForm($edit, 'Continue');
    $session->assertWaitOnAjaxRequest();
    $edit = [
      'title' => 'Test External Link',
      'url' => 'harvard.edu',
    ];
    $this->submitForm($edit, 'Finish');
    $session->assertWaitOnAjaxRequest();
    $session->pageTextContains('Url is invalid.');
  }

  /**
   * Tests Home link creation.
   */
  public function testHomeLinkAdd(): void {
    // Test adding home link.
    $this->visit('/test-menu/cp/build/menu');
    $session = $this->assertSession();
    $page = $this->getCurrentPage();

    // Delete Home link first to verify link by href later.
    $link = $page->find('css', '.Home #delete_menu_link');
    $link->click();
    $session->waitForElementVisible('css', '.cp-delete-menu-link');
    $this->submitForm([], 'Confirm');
    $session->waitForElementVisible('css', '#cp-build-menu-table');

    // Add home link.
    $link = $page->find('css', '#add_new_link');
    $link->click();
    $session->waitForElementVisible('css', '.cp-menu-link-add-form');
    $edit = [
      'link_type' => 'home',
    ];
    $this->submitForm($edit, 'Continue');
    $session->assertWaitOnAjaxRequest();
    $edit = [
      'title' => 'Test Home Link',
    ];
    $this->submitForm($edit, 'Finish');
    $session->assertWaitOnAjaxRequest();
    $session->linkExists('Test Home Link');
    $session->linkByHrefExists('/test-menu/');
  }

  /**
   * Tests Adding heading.
   */
  public function testHeadingLinkAdd(): void {
    // Test adding home link.
    $this->visit('/test-menu/cp/build/menu');
    $session = $this->assertSession();
    $page = $this->getCurrentPage();

    // Add home link.
    $link = $page->find('css', '#add_new_link');
    $link->click();
    $session->waitForElementVisible('css', '.cp-menu-link-add-form');
    $edit = [
      'link_type' => 'menu_heading',
    ];
    $this->submitForm($edit, 'Continue');
    $session->assertWaitOnAjaxRequest();
    $edit = [
      'title' => 'Test Menu Heading Link',
    ];
    $this->submitForm($edit, 'Finish');
    $session->assertWaitOnAjaxRequest();
    $session->waitForElementVisible('css', '#cp-build-menu-table');
    $session->linkExists('Test Menu Heading Link');
    $session->linkByHrefExists('');
  }

}
