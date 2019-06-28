<?php

namespace Drupal\Tests\os_events\ExistingSiteJavascript;

/**
 * Signup Modal Form test.
 *
 * @group functional-javascript
 * @group events
 */
class ModalSignupFormTest extends EventsJavascriptTestBase {
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
   * Test if Modal form opens on click.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testModalOpen() {

    $url = $this->createEventFunctionalJs();
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

    $url = $this->createEventFunctionalJs();
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
    $web_assert->pageTextContains('"test@example.com" is already registered for the event.');
  }

  /**
   * Tests Registration creation.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testRegistrationCreation() {

    $url = $this->createEventFunctionalJs();
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

}
