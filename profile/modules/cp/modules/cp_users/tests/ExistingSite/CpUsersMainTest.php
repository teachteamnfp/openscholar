<?php

namespace Drupal\Tests\cp_users\ExistingSite;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Test\AssertMailTrait;
use Drupal\Tests\vsite\ExistingSiteJavascript\VsiteExistingSiteJavascriptTestBase;

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
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    /** @var ConfigFactoryInterface $configFactory */
    $configFactory = $this->container->get('config.factory');
    $config = $configFactory->get('system.mail');
    $this->oldMailHandler = $config->get('interface.default');
    $config->set('interface.default', 'test_mail_collector')->save();

    $this->group = $this->createGroup([
      'type' => 'personal',
      'uid' => 1,
      'path' => [
        'alias' => '/site01',
      ],
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function tearDown() {
    /** @var ConfigFactoryInterface $configFactory */
    $configFactory = $this->container->get('config.factory');
    $config = $configFactory->get('system.mail');
    $config->set('interface.default', $this->oldMailHandler)->save();

    parent::tearDown();
  }

  /**
   * Tests for adding and removing users.
   */
  public function testAddExistingUser() {
    try {
      $account = $this->entityTypeManager->getStorage('user')->load(1);
      $account->passRaw = 'admin';
      $this->drupalLogin($account);
      $username = $this->randomString();
      $user = $this->createUser([], $username, FALSE);

      $this->visit('/site01/cp/users');
      $page = $this->getCurrentPage();
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
      $this->assertContains('/site01/cp/users', $this->getSession()->getCurrentUrl());
      $this->assertTrue($page->hasContent($username), "Username $username not found on page.");


      $remove = $page->find('xpath', '//tr/td[contains(.,"' . $username . '")]/following-sibling::td/a[contains(.,"Remove")]');
      $this->assertNotNull($remove, "Remove link for $username not found.");
      $remove->click();
      $this->assertSession()->waitForElement('css', '#drupal-modal--content');
      $page->pressButton('Confirm');
      $this->assertSession()->assertWaitOnAjaxRequest();
      $this->assertFalse($page->hasContent($username), "Username $username still found on page.");
    }
    catch (\Exception $e) {
      \file_put_contents(REQUEST_TIME . '.jpg', $this->getSession()->getScreenshot());
      $page = $this->getCurrentPage();
      \file_put_contents(REQUEST_TIME . '.txt', $page->getContent());
      $this->fail(\get_class($e) . ' in test: ' . $e->getMessage() . "\n" . $e->getFile() . ':' . $e->getLine());
    }
  }

}
