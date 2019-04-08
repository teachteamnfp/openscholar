<?php

namespace Drupal\Tests\os_events\ExistingSiteJavascript;

use Drupal\Component\Datetime\DateTimePlus;
use weitzman\DrupalTestTraits\ExistingSiteWebDriverTestBase;

/**
 * Signup Modal Form test.
 *
 * @group functional-javascript
 */
class ModalSignupFormTest extends ExistingSiteWebDriverTestBase {
  /**
   * Simple user.
   *
   * @var \Drupal\user\Entity\User|false
   */
  protected $simpleUser;
  /**
   * Admin User.
   *
   * @var \Drupal\user\Entity\User|false
   */
  protected $adminUser;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->simpleUser = $this->createUser(['access control panel']);
    $this->adminUser = $this->createUser([], '', TRUE);

  }

  /**
   * Test if Modal form opens on click.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testModalOpen() {

    $url = $this->createEvent();
    $this->drupalLogin($this->simpleUser);

    $this->visit($url);

    $web_assert = $this->assertSession();
    $web_assert->statusCodeEquals(200);
    $web_assert->elementNotExists('css', '#signup-modal-form');

    $page = $this->getCurrentPage();
    $signup_link = $page->findById('events_signup_modal_form');
    $signup_link->click();
    $web_assert->waitForElementVisible('css', '#signup-modal-form');
  }

  /**
   * Test Modal form submission and validation.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testModalFormSubmission() {

    $url = $this->createEvent();
    $this->drupalLogin($this->simpleUser);

    $this->visit($url);
    $web_assert = $this->assertSession();

    $page = $this->getCurrentPage();
    $signup_link = $page->findById('events_signup_modal_form');
    $signup_link->click();
    $web_assert->waitForElementVisible('css', '#signup-modal-form');

    // Positive case.
    $edit = [
      'email' => 'test@example.com',
      'full_name' => $this->randomString(),
      'department' => $this->randomString(),
    ];
    $this->submitForm($edit, 'Signup');
    $web_assert->assertWaitOnAjaxRequest();
    $web_assert->elementNotExists('css', '#signup-modal-form');

    // Negative Case to test validation.
    $signup_link->click();
    $web_assert->waitForElementVisible('css', '#signup-modal-form');
    $edit = [
      'email' => 'test@example.com',
      'full_name' => $this->randomString(),
      'department' => $this->randomString(),
    ];
    $this->submitForm($edit, 'Signup');
    $web_assert->assertWaitOnAjaxRequest();
    $web_assert->elementExists('css', '#signup-modal-form');
    $web_assert->pageTextContains('User is already registered for this event.');
  }

  /**
   * Tests Registration creation.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testRegistrationCreation() {

    $url = $this->createEvent();
    $this->visit($url);

    $web_assert = $this->assertSession();

    $page = $this->getCurrentPage();
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
    $new_page = $this->getCurrentPage();
    $new_page->clickLink('Registrations');
    $this->assertSession()->pageTextContains('test@example.com');
  }

  /**
   * Creates an Event entity.
   *
   * @return string
   *   The url to newly created entity.
   */
  protected function createEvent() {
    $date = new DateTimePlus('+5 days');

    $this->drupalLogin($this->adminUser);
    $this->drupalGet('node/add/events');
    $edit = [
      'title[0][value]' => $this->randomString(),
      'field_recurring_date[0][value][date]' => $date->format("Y-m-d"),
      'field_recurring_date[0][value_all_day]' => TRUE,
      'field_signup[value]' => TRUE,
    ];
    $this->submitForm($edit, 'edit-submit');
    $node_url = $this->getUrl();
    return $node_url;
  }

}
