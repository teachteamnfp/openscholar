<?php

namespace Drupal\Tests\cp_users\ExistingSite;

use Drupal\Core\Test\AssertMailTrait;
use Drupal\purl\Plugin\ModifierIndex;
use Drupal\Tests\vsite\ExistingSiteJavascript\VsiteExistingSiteJavascriptTestBase;
use Drupal\user\UserInterface;

/**
 * Class CpUsersMainTests.
 *
 * @group functional-javascript
 * @group wip
 * @package Drupal\Tests\cp_users\ExistingSite
 */
class CpUsersMainTest extends VsiteExistingSiteJavascriptTestBase {

  use AssertMailTrait;

  /**
   * The group tests are being run in.
   *
   * @var \Drupal\group\Entity\GroupInterface
   */
  protected $group;

  /**
   * The mail interface we're replacing. We need to put it back when we're done.
   *
   * @var string
   */
  protected $oldMailHandler;

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    /** @var \Drupal\Core\Config\ConfigFactoryInterface $configFactory */
    $this->configFactory = $this->container->get('config.factory');
    $config = $this->configFactory->getEditable('system.mail');
    $this->oldMailHandler = $config->get('interface.default');
    $config->set('interface.default', 'test_mail_collector')->save();

    $this->group = $this->createGroup([
      'type' => 'personal',
      'uid' => 1,
      'path' => [
        'alias' => '/' . $this->randomMachineName()
      ],
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function tearDown() {
    /** @var \Drupal\Core\Config\ConfigFactoryInterface $configFactory */
    $configFactory = $this->container->get('config.factory');
    $config = $configFactory->getEditable('system.mail');
    $config->set('interface.default', $this->oldMailHandler)->save();

    parent::tearDown();
  }

  /**
   * Tests for adding and removing users.
   */
  public function testAddExistingUser() {
    $modifierIndex = new ModifierIndex();
    $modifiers = $modifierIndex->findAll();
    $modifier = '';
    foreach ($modifiers as $m) {
      $modifier = $m->getModifierKey();
      if ($modifier) {
        break;
      }
    }

    try {
      $account = $this->entityTypeManager->getStorage('user')->load(1);
      $account->passRaw = 'admin';
      $this->drupalLogin($account);
      $username = $this->randomString();
      $user = $this->createUser([], $username, FALSE);

      $this->visit('/'.$modifier.'/cp/users');
      $this->assertContains('/'.$modifier.'/cp/users', $this->getSession()->getCurrentUrl(), "First url check, on " . $this->getSession()->getCurrentUrl());
      $page = $this->getCurrentPage();
      $link = $page->findLink('+ Add a member');
      $this->assertContains('/'.$modifier.'/cp/users/add', $link->getAttribute('href'), "Add link is not in the vsite.");
      $page->clickLink('+ Add a member');
      $this->assertSession()->waitForElement('css', '#drupal-modal--content');
      $page->clickLink('Add an Existing User');
      $page->fillField('member-entity', substr($username, 0, 3));
      $this->assertSession()->waitOnAutocomplete();
      $element = $page->find('css', '#ui-id-2');
      $this->assertNotNull($element, 'cannot find ui-id-2');
      $element->click();

      $page->selectFieldOption('role', 'personal-member');
      $page->pressButton("Save");
      $this->assertSession()->assertWaitOnAjaxRequest();
      //$this->visit('/site01/cp/users');
      $this->assertContains('/'.$modifier.'/cp/users', $this->getSession()->getCurrentUrl(), "Not on the correct page, on " . $this->getSession()->getCurrentUrl());
      $this->assertTrue($page->hasContent($username), "Username $username not found on page.");

      // $this->assertMail('id', '');.
      $remove = $page->find('xpath', '//tr/td[contains(.,"' . $username . '")]/following-sibling::td/a[contains(.,"Remove")]');
      $this->assertNotNull($remove, "Remove link for $username not found.");
      $remove->click();
      $this->assertSession()->waitForElement('css', '#drupal-modal--content');
      $page->pressButton('Confirm');
      $this->assertSession()->assertWaitOnAjaxRequest();
      $this->assertFalse($page->hasContent($username), "Username $username still found on page.");

      // $this->assertMail('id', CP_USERS_DELETE_FROM_GROUP, "Mail " . CP_USERS_DELETE_FROM_GROUP . " not sent.");.
    }
    catch (\Exception $e) {
      \file_put_contents(REQUEST_TIME . '.jpg', $this->getSession()->getScreenshot());
      $page = $this->getCurrentPage();
      \file_put_contents(REQUEST_TIME . '.txt', $page->getContent());
      $this->fail(\get_class($e) . ' in test: ' . $e->getMessage() . "\n" . $e->getFile() . ':' . $e->getLine());
    }
  }

  /**
   * Tests for adding a user new to the site.
   */
  public function testNewUser() {
    $modifierIndex = new ModifierIndex();
    $modifiers = $modifierIndex->findAll();
    $modifier = '';
    foreach ($modifiers as $m) {
      $modifier = $m->getModifierKey();
      if ($modifier) {
        break;
      }
    }
    $settings = $this->configFactory->getEditable('cp_users.settings');
    try {
      $this->assertFalse($settings->get('disable_user_creation'), "User creation setting is wrong.");

      $account = $this->entityTypeManager->getStorage('user')->load(1);
      $account->passRaw = 'admin';
      $this->drupalLogin($account);

      $this->visit('/'.$modifier.'/cp/users');
      $this->assertContains('/'.$modifier.'/cp/users', $this->getSession()->getCurrentUrl());
      $page = $this->getCurrentPage();
      $page->clickLink('+ Add a member');
      $this->assertSession()->waitForElement('css', '#drupal-modal--content');
      $page->clickLink('Add New User');
      $page->fillField('First Name', 'test');
      $page->fillField('Last Name', 'user');
      $page->fillField('Username', 'test-user');
      $page->fillField('E-mail Address', 'test-user@localhost.com');
      $page->selectFieldOption('role', 'personal-member');
      $page->pressButton('Save');
      $this->assertSession()->assertWaitOnAjaxRequest();
      $this->assertContains('/'.$modifier.'/cp/users', $this->getSession()->getCurrentUrl(), "Not on correct page after redirect.");
      $this->assertTrue($page->hasContent('test-user'), "Test-user not added to site.");

      $settings->set('disable_user_creation', 1);
      $settings->save();

      $page->clickLink('+ Add a member');
      $this->assertSession()->waitForElement('css', '#drupal-modal--content');
      $this->assertSession()->linkNotExists('Add New User', "Add New User is still on page.");

      $page->clickLink('Change Owner');
      $this->assertSession()->waitForElement('css', '#drupal-modal--content');
      $page->selectFieldOption('new_owner', 'test-user');
      $page->pressButton('Save');
      $this->assertSession()->assertWaitOnAjaxRequest();
      /** @var UserInterface $user */
      $user = user_load_by_name('test-user');
      $this->assertEquals($user->id(), $this->group->getOwnerId(), "Owner did not change.");
    }
    catch (\Exception $e) {
      \file_put_contents(REQUEST_TIME . '.jpg', $this->getSession()->getScreenshot());
      $page = $this->getCurrentPage();
      \file_put_contents(REQUEST_TIME . '.txt', $page->getContent());
      throw $e;
    }
    finally {
      if ($user = \user_load_by_name('test-user')) {
        $this->markEntityForCleanup($user);
      }

      $settings->set('disable_user_creation', 0);
      $settings->save();
    }
  }

}
