<?php

namespace Drupal\Tests\os_events\ExistingSite;

use Drupal\Component\Datetime\DateTimePlus;

/**
 * Class ManageRegistrationsTest.
 *
 * @group functional
 * @group events
 *
 * @covers ::os_events_menu_local_tasks_alter
 *
 * @package Drupal\Tests\os_events\ExistingSite
 */
class ManageRegistrationsTest extends EventsTestBase {

  /**
   * Test Manage Registrations tab when Signup is checked.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Behat\Mink\Exception\ResponseTextException
   */
  public function testManageRegistrationsTabSignupChecked() {
    $this->createEvent(TRUE);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Manage Registrations');

  }

  /**
   * Test Manage Registrations tab when Signup is unchecked.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Behat\Mink\Exception\ResponseTextException
   */
  public function testManageRegistrationsTabSignupUnChecked() {
    $this->createEvent(FALSE);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextNotContains('Manage Registrations');

  }

  /**
   * Test event page for registration block.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testEventsPage() {
    $this->createEvent(TRUE);
    $this->assertSession()->elementExists('css', '#events_signup_modal_form');
  }

  /**
   * Test registration block is not visible before open date.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testRegistrationOpenDate() {

    $this->createEvent(TRUE);
    $node_url = $this->getUrl();
    $this->assertSession()->elementExists('css', '#events_signup_modal_form');
    // Set a future open date.
    $future_date = new DateTimePlus('+2 day');
    $this->clickLink('Manage Registrations');
    $edit = [
      'field_open_date[0][value][date]' => $future_date->format("Y-m-d"),
    ];
    $this->submitForm($edit, 'edit-submit');
    $this->drupalGet($node_url);
    $this->assertSession()->elementNotExists('css', '#events_signup_modal_form');
  }

  /**
   * Test registration block is not visible after close date.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testRegistrationCloseDate() {

    $this->createEvent(TRUE);
    $node_url = $this->getUrl();
    $this->assertSession()->elementExists('css', '#events_signup_modal_form');
    // Set a past close date.
    $future_date = new DateTimePlus('-2 day');
    $past_date = new DateTimePlus('yesterday midnight');
    $this->clickLink('Manage Registrations');
    $edit = [
      'field_open_date[0][value][date]' => $future_date->format('Y-m-d'),
      'field_close_[0][value][date]' => $past_date->format("Y-m-d H:i:s"),
    ];
    $this->submitForm($edit, 'edit-submit');
    $this->drupalGet($node_url);
    $this->assertSession()->elementNotExists('css', '#events_signup_modal_form');
  }

  /**
   * Test Registrations List view.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testRegistrationsListPage() {
    $this->createEvent(TRUE);
    $this->clickLink('Manage Registrations');
    $this->clickLink('Registrations');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->elementExists('css', '.view-id-rng_registrations_node');
  }

  /**
   * Test Email Registrants page.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testEmailRegistrantsPage() {
    $this->createEvent(TRUE);
    $this->clickLink('Manage Registrations');
    $this->clickLink('Email Registrants');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->elementExists('css', '.custom-message-to-registrations');
  }

}
