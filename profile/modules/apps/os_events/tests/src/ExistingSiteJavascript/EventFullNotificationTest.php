<?php

namespace Drupal\Tests\os_events\ExistingSiteJavascript;

/**
 * EventFullNotificationTest.
 *
 * @group functional-javascript
 */
class EventFullNotificationTest extends EventsJavascriptTestBase {
  /**
   * Entity type manager service.
   *
   * @var object
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->entityTypeManager = $this->container->get('entity_type.manager');
  }

  /**
   * Tests  Registration full email is triggered.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testRegistrationFullEmail() {

    $url = $this->createRecurringEvent();
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
    $edit = [
      'rng_capacity[0][unlimited_number][unlimited_number]' => TRUE,
      'rng_capacity[0][unlimited_number][number]' => 2,
    ];
    $this->submitForm($edit, 'edit-submit');
    $this->visit($url);
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

    $entityStorage = $this->entityTypeManager->getStorage('courier_message_queue_item');
    $result = $entityStorage->getQuery()
      ->condition('identity.target_id', $this->adminUser->id())
      ->condition('identity.target_type', $this->adminUser->getEntityTypeId())
      ->execute();
    $this->assertNotNull($result);
  }

}
