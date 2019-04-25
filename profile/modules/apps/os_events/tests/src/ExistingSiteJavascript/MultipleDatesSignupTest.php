<?php

namespace Drupal\Tests\os_events\ExistingSiteJavascript;

use Drupal\Component\Datetime\DateTimePlus;

/**
 * Class MultipleDatesSignupTest.
 *
 * @group functional-javascript
 * @group events
 * @package Drupal\Tests\os_events\ExistingSiteJavascript
 */
class MultipleDatesSignupTest extends EventsJavascriptTestBase {
  /**
   * Simple user.
   *
   * @var \Drupal\user\Entity\User|false
   */
  protected $simpleUser;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->simpleUser = $this->createUser();
  }

  /**
   * Test if Select List exists.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testSelectList() {

    $url = $this->createRecurringEvent();
    $this->drupalLogin($this->simpleUser);

    $this->visit($url);

    $web_assert = $this->assertSession();
    $web_assert->statusCodeEquals(200);
    $web_assert->selectExists('rdates');
  }

  /**
   * Test Select List selections change the Signup link via Ajax.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   */
  public function testSelection() {
    $url = $this->createRecurringEvent();
    $this->drupalLogin($this->simpleUser);

    $this->visit($url);

    $web_assert = $this->assertSession();
    $page = $this->getCurrentPage();

    $hrefBefore = $page->findById('events_signup_modal_form')->getAttribute('href');

    $dateTimeObject = new DateTimePlus('+12 day');
    $dateString = $dateTimeObject->format('l, F j, Y');
    $page->selectFieldOption('rdates', $dateString);
    $web_assert->assertWaitOnAjaxRequest();

    $hrefAfter = $page->findById('events_signup_modal_form')->getAttribute('href');
    $this->assertNotEquals($hrefAfter, $hrefBefore);
  }

  /**
   * Tests Registration List view filter.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testRegistrationListFilter() {

    $url = $this->createRecurringEvent();
    $this->visit($url);

    $web_assert = $this->assertSession();
    $page = $this->getCurrentPage();

    $dateTimeObject = new DateTimePlus('+12 day');
    $dateString = $dateTimeObject->format('l, F j, Y');
    $page->selectFieldOption('rdates', $dateString);
    $web_assert->assertWaitOnAjaxRequest();

    $signup_link = $page->findById('events_signup_modal_form');
    $signup_link->click();
    $web_assert->waitForElementVisible('css', '#signup-modal-form');

    $edit = [
      'email' => 'test@example.com',
      'full_name' => $this->randomString(),
      'department' => $this->randomString(),
    ];

    $this->submitForm($edit, 'Signup');
    $web_assert->assertWaitOnAjaxRequest();
    $page->clickLink('Manage Registrations');
    $page->clickLink('Registrations');
    $page->selectFieldOption('field_for_date_value', $dateString);
    $page->pressButton('Apply');
    $this->assertSession()->pageTextContains('test@example.com');
  }

}
