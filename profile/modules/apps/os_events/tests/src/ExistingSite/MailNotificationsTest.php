<?php

namespace Drupal\Tests\os_events\ExistingSite;

use Drupal\Component\Datetime\DateTimePlus;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\rng\Entity\Registrant;
use Drupal\rng\Entity\Registration;
use Drupal\rng_contact\Entity\RngContact;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * MailNotificationsTest.
 *
 * @group kernel
 * @group other
 */
class MailNotificationsTest extends ExistingSiteBase {

  /**
   * Entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeInterface
   */
  protected $entityTypeManager;

  /**
   * The created node.
   *
   * @var \Drupal\node\Entity\Node
   */
  protected $event;

  /**
   * Create Events type node.
   */
  public function setUp() {
    parent::setUp();

    $date = new DateTimePlus('+5 day');

    $this->event = $this->createNode([
      'type' => 'events',
      'field_location' => 'London',
      'title[0][value]' => 'Test',
      'field_signup[value]' => TRUE,
    ]);
    $this->event->field_recurring_date->value = $date->format("Y-m-d H:i:s");

    $this->entityTypeManager = $this->container->get('entity_type.manager');
    $this->configManager = $this->container->get('config.factory');
    $this->formatter = $this->container->get('date.formatter');
    $this->mailNotifications = $this->container->get('os_events.mail_notifications');

  }

  /**
   * Tests Signup Mail confirmation.
   */
  public function testSignupConfirmationMail() {

    $registrant = $this->createRegistration();
    $id = $registrant->getIdentityId();
    $entityStorage = $this->entityTypeManager->getStorage('courier_message_queue_item');
    $result = $entityStorage->getQuery()
      ->condition('identity.target_id', $id['entity_id'])
      ->condition('identity.target_type', $id['entity_type'])
      ->execute();
    $this->assertNotNull($result);
  }

  /**
   * Tests Event cancellation.
   */
  public function testEventCancellationMail() {
    $registrant = $this->createRegistration();
    $id = $registrant->getIdentityId();
    $this->deleteMqi();
    $this->event->delete();
    $entityStorage = $this->entityTypeManager->getStorage('courier_message_queue_item');
    $result = $entityStorage->getQuery()
      ->condition('identity.target_id', $id['entity_id'])
      ->condition('identity.target_type', $id['entity_type'])
      ->execute();
    $this->assertNotNull($result);
  }

  /**
   * Tests when a update is done mail is triggered and registrations updated.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testUpdateMail() {
    $registrant = $this->createRegistration();
    $id = $registrant->getIdentityId();
    $registration = $registrant->getRegistration();
    $oldRegistrationDate = strtotime($registration->field_for_date->value);

    $this->deleteMqi();
    $this->mailNotifications->sendUpdateNotificationEmail($this->event);
    $entityStorage = $this->entityTypeManager->getStorage('courier_message_queue_item');
    $result = $entityStorage->getQuery()
      ->condition('identity.target_id', $id['entity_id'])
      ->condition('identity.target_type', $id['entity_type'])
      ->execute();
    $this->assertNotNull($result);

    $date = new DateTimePlus('+7 day');
    $this->event->field_recurring_date->value = $date->format("Y-m-d H:i:s");

    $this->drupalLogin($this->createUser([], [], TRUE));
    $url = 'node/' . $this->event->id() . '/edit';
    $this->drupalGet($url);

    $data = [
      'field_recurring_date[0][day_start]' => $date->format("Y-m-d"),
      'field_recurring_date[0][time_start]' => '00:00:00',
    ];

    $this->submitForm($data, 'edit-submit');

    // Check if old Registration date and new Registration date is not same.
    $registration = $registrant->getRegistration();
    $newRegistrationDate = strtotime($registration->field_for_date->value);
    $this->assertNotEquals($oldRegistrationDate, $newRegistrationDate);
  }

  /**
   * Tests setting up reminder does a scheduler entry.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testRuleSchedulerEntry() {
    $date = new DrupalDateTime('+7 day');
    $values['field_send_reminder'] = [['value' => $date]];

    $this->mailNotifications->setUpReminderEmail($values, $this->event);

    $entityStorage = $this->entityTypeManager->getStorage('rng_rule');
    $result = $entityStorage->getQuery()
      ->condition('event.target_id', $this->event->id())
      ->condition('event.target_type', 'node')
      ->execute();
    $this->assertNotNull($result);

    // Test Rule Schedule Delete.
    $this->mailNotifications->disableReminderEmail($this->event);
    $result = $entityStorage->getQuery()
      ->condition('event.target_id', $this->event->id())
      ->condition('event.target_type', 'node')
      ->condition('status', 0)
      ->execute();
    $this->assertNotNull($result);

  }

  /**
   * Tests Registrations broadcast.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testRegistrationsBroadcast() {
    $registrant1 = $this->createRegistration();
    $registrant2 = $this->createRegistration();
    $id1 = $registrant1->getIdentityId();
    $id2 = $registrant2->getIdentityId();
    $this->deleteMqi();

    $this->drupalLogin($this->createUser([], [], TRUE));
    $url = 'node/' . $this->event->id() . '/event/registrations/broadcast';
    $this->drupalGet($url);

    $data = [
      'subject[0][value]' => 'This is a test.',
      'body[0][value]' => 'This is a broadcast test.',
    ];

    $this->submitForm($data, 'edit-submit');

    $entityStorage = $this->entityTypeManager->getStorage('courier_message_queue_item');
    $result1 = $entityStorage->getQuery()
      ->condition('identity.target_id', $id1['entity_id'])
      ->condition('identity.target_type', $id1['entity_type'])
      ->execute();
    $this->assertNotNull($result1);
    $result2 = $entityStorage->getQuery()
      ->condition('identity.target_id', $id2['entity_id'])
      ->condition('identity.target_type', $id2['entity_type'])
      ->execute();
    $this->assertNotNull($result2);
  }

  /**
   * Create Registrations for nodes.
   *
   * @return \Drupal\Core\Entity\EntityInterface|Registrant
   *   Registrant entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function createRegistration() {
    $date = $this->event->field_recurring_date->value;
    $timestamp = strtotime($date);
    $date = $this->formatter->format($timestamp, 'custom', DateTimeItemInterface::DATETIME_STORAGE_FORMAT);

    $registration = Registration::create([
      'type' => 'signup',
      'event' => $this->event,
      'field_for_date' => ['value' => $date],
    ]);
    $registration->save();

    $identity = RngContact::create([
      'type' => 'anonymous_',
      'label' => 'TestUser',
      'field_email' => 'test@example.com',
      'field_department' => $this->randomString(),
    ]);
    $identity->save();

    $registrant = Registrant::create([
      'type' => 'registrant',
      'registration' => $registration,
      'identity' => $identity,
    ]);
    $registrant->save();

    $this->markEntityForCleanup($registration);
    $this->markEntityForCleanup($identity);
    $this->markEntityForCleanup($registrant);

    return $registrant;
  }

  /**
   * Clear the old queue for new assertions.
   */
  protected function deleteMqi() {
    $entityStorage = $this->entityTypeManager->getStorage('courier_message_queue_item')->loadMultiple();
    foreach ($entityStorage as $mqi) {
      $mqi->delete();
    }
  }

}
