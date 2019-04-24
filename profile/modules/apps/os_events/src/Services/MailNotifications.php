<?php

namespace Drupal\os_events\Services;

use Drupal\Core\Action\ActionManager;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Form\FormState;
use Drupal\courier\Entity\GlobalTemplateCollection;
use Drupal\courier\Service\CourierManagerInterface;
use Drupal\courier\Service\GlobalTemplateCollectionManager;
use Drupal\os_events\MailNotificationsInterface;
use Drupal\rng\Entity\Rule;
use Drupal\rng\Entity\RuleComponent;
use Drupal\rng\Entity\RuleSchedule;
use Drupal\rng\EventManagerInterface;

/**
 * Class DefaultMailTemplate.
 *
 * @package Drupal\os_events\Services
 */
class MailNotifications implements MailNotificationsInterface {


  /**
   * Entity Manager type service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityManager;
  /**
   * Action Manager service.
   *
   * @var object
   */
  protected $actionPlugin;
  /**
   * Courier Manager service.
   *
   * @var \Drupal\courier\Service\CourierManagerInterface
   */
  protected $courierManager;
  /**
   * Global template collection manager.
   *
   * @var \Drupal\courier\Entity\GlobalTemplateCollectionManager
   */
  protected $gtcManager;
  /**
   * Event Manager service.
   *
   * @var \Drupal\rng\EventManagerInterface
   */
  protected $eventManager;

  /**
   * Constructs a new DefaultMailTemplate object.
   */
  public function __construct(ActionManager $actionManager, EntityTypeManager $entityManager, CourierManagerInterface $courierManager, GlobalTemplateCollectionManager $gtcManager, EventManagerInterface $eventManager) {
    $this->actionPlugin = $actionManager->createInstance('rng_courier_message');
    $this->entityManager = $entityManager;
    $this->courierManager = $courierManager;
    $this->gtcManager = $gtcManager;
    $this->eventManager = $eventManager;
  }

  /**
   * {@inheritdoc}
   */
  public function sendConfirmationEmail(EntityInterface $registrant) {

    $gtc = GlobalTemplateCollection::load('event_registration_confirmation');
    $ltc = $this->gtcManager->getLocalCollection($gtc);

    $options = [];
    /** @var \Drupal\rng\RegistrationInterface $registration */
    $registration = $registrant->getRegistration();
    $event = $registration->getEvent();
    if ($event) {
      $eventMeta = $this->eventManager->getMeta($event);
      $options['channels']['courier_email']['reply_to'] = $eventMeta->getReplyTo();
      $ltc->setTokenValue($event->getEntityTypeId(), $event);
    }
    $ltc->setTokenValue('registration', $registration);
    $ltc->setTokenOption('clear', TRUE);
    $this->courierManager->sendMessage($ltc, $registrant->getIdentity(), $options);
  }

  /**
   * {@inheritdoc}
   */
  public function sendUpdateNotificationEmail(EntityInterface $event) {

    $gtc = GlobalTemplateCollection::load('event_update_notification');
    $ltc = $this->gtcManager->getLocalCollection($gtc);

    $options = [];

    $eventMeta = $this->eventManager->getMeta($event);
    $options['channels']['courier_email']['reply_to'] = $eventMeta->getReplyTo();
    $ltc->setTokenValue($event->getEntityTypeId(), $event);

    foreach ($eventMeta->getRegistrations() as $registration) {
      $collection = clone $ltc;
      $collection->setTokenValue('registration', $registration);
      $collection->setTokenOption('clear', TRUE);
      $registrant = $registration->getRegistrants();
      $registrant = array_shift($registrant);
      $this->courierManager->sendMessage($collection, $registrant->getIdentity(), $options);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function sendCancelNotificationEmail(EntityInterface $event) {

    $gtc = GlobalTemplateCollection::load('event_cancel_notification');
    $ltc = $this->gtcManager->getLocalCollection($gtc);

    $options = [];

    $eventMeta = $this->eventManager->getMeta($event);
    $options['channels']['courier_email']['reply_to'] = $eventMeta->getReplyTo();
    $ltc->setTokenValue($event->getEntityTypeId(), $event);

    foreach ($eventMeta->getRegistrations() as $registration) {
      $collection = clone $ltc;
      $collection->setTokenValue('registration', $registration);
      $collection->setTokenOption('clear', TRUE);
      $registrant = $registration->getRegistrants();
      $registrant = array_shift($registrant);
      $this->courierManager->sendMessage($collection, $registrant->getIdentity(), $options);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function sendEventFullEmail(EntityInterface $event) {

    $gtc = GlobalTemplateCollection::load('event_full_notification');
    $ltc = $this->gtcManager->getLocalCollection($gtc);

    $ltc->setTokenValue($event->getEntityTypeId(), $event);
    $ltc->setTokenOption('clear', TRUE);
    $identity = $event->getOwner();
    $this->courierManager->sendMessage($ltc, $identity);

  }

  /**
   * {@inheritdoc}
   */
  public function setUpReminderEmail(array $values, EntityInterface $event) {

    $meta = $this->eventManager->getMeta($event);
    $date = array_shift($values['field_send_reminder'])['value'];
    $timestamp = $date->getPhpDateTime()->getTimestamp();
    $ruleExists = FALSE;

    // Check if rule already exists, just modify or create the schedule.
    $rules = $meta->getRules('rng:custom:date', FALSE, NULL);
    foreach ($rules as $rule) {
      $ruleExists = TRUE;
      $ruleComponents = $rule->getConditions();
      if ($ruleComponents) {
        $ruleComponent = array_shift($ruleComponents);
        $pluginId = $ruleComponent->getPluginId();
        if ($pluginId == 'rng_rule_scheduler') {
          $config = $ruleComponent->getConfiguration();
          $ruleSchedulerId = $config['rng_rule_scheduler'];
          $ruleScheduler = $this->entityManager->getStorage('rng_rule_scheduler')->load($ruleSchedulerId);
          if ($ruleScheduler) {
            // If schedule already exists override it.
            $ruleScheduler->setDate($timestamp)
              ->save();
            return;
          }
          else {
            // If schedule doesn't exist create one.
            $this->createRuleScheduler($ruleComponent, $timestamp);
          }
        }
        // If rule is inactive set it to Active for reusabiltity.
        $rule->setIsActive(TRUE)
          ->save();
      }
    }
    // If rule does not exists, create a template, rule and scheduler.
    if (!$ruleExists) {
      $context = $this->entityManager->getStorage('courier_context')
        ->load('rng_registration_' . $event->getEntityTypeId());

      // Create a new template collection by faking a form submission.
      $dummy = [];
      $this->actionPlugin->submitConfigurationForm($dummy, new FormState());
      $templateCollection = $this->actionPlugin->getTemplateCollection();
      $templateCollection->setContext($context)
        ->setOwner($event)
        ->save();

      // Get the email templates so we can modify them.
      $templates = $templateCollection->getTemplates('courier_email');
      /** @var \Drupal\courier\Entity\Email $mailTemplate */
      $mailTemplate = $templates[0];

      // Set the template.
      $message = 'Hello,</br></br>';
      $message .= 'This is only a friendly reminder that you registered the event <a href="[node:url]">[node:title]</a></br>';
      $message .= '</br></br></br>';
      $message .= '[university:title], [university:address]';

      $mailTemplate->setSubject('Reminder for [node:title]');
      $mailTemplate->setBody($message);
      $mailTemplate->save();

      // Create a RuleComponent action and template.
      $action = RuleComponent::create([])
        ->setPluginId($this->actionPlugin->getPluginId())
        ->setConfiguration(['template_collection' => $templateCollection->id()])
        ->setType('action');

      // Create a Rule and set it to active.
      $rule = Rule::create([
        'event' => ['entity' => $event],
        'trigger_id' => 'rng:custom:date',
      ]);
      $rule->setIsActive(TRUE);
      $rule->save();
      $action->setRule($rule)->save();

      // Create a RuleComponent condition which is scheduler.
      $ruleComponent = RuleComponent::create()
        ->setRule($rule)
        ->setType('condition')
        ->setPluginId('rng_rule_scheduler');
      $ruleComponent->save();

      // Create the schedule.
      $this->createRuleScheduler($ruleComponent, $timestamp);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function disableReminderEmail(EntityInterface $event) {
    $meta = $this->eventManager->getMeta($event);
    $rules = $meta->getRules('rng:custom:date', FALSE, NULL);
    foreach ($rules as $rule) {
      $ruleComponents = $rule->getConditions();
      if ($ruleComponents) {
        $ruleComponent = array_shift($ruleComponents);
        $pluginId = $ruleComponent->getPluginId();
        if ($pluginId == 'rng_rule_scheduler') {
          $config = $ruleComponent->getConfiguration();
          $ruleSchedulerId = $config['rng_rule_scheduler'];
          $ruleScheduler = $this->entityManager->getStorage('rng_rule_scheduler')->load($ruleSchedulerId);
          if ($ruleScheduler) {
            // If schedule exists delete it.
            $ruleScheduler->delete();
          }
        }
        // Set Rule as inactive.
        $rule->setIsActive(FALSE)
          ->save();
      }
    }
  }

  /**
   * Creates Rule Scheduler Entity.
   *
   * @param \Drupal\rng\Entity\RuleComponent $ruleComponent
   *   Rule component entity.
   * @param string $timestamp
   *   The trigger date.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function createRuleScheduler(RuleComponent $ruleComponent, $timestamp) {
    $ruleScheduler = RuleSchedule::create([
      'component' => $ruleComponent->id(),
    ]);
    $ruleScheduler->save();

    // Save the ID and date into config.
    $ruleComponent->setConfiguration([
      'rng_rule_scheduler' => $ruleScheduler->id(),
      'rng_rule_component' => $ruleComponent->id(),
      'date' => $timestamp,
    ]);
    $ruleComponent->save();

    // Mirror the date into the scheduler.
    $ruleScheduler->setDate($timestamp);
    $ruleScheduler->save();
  }

}
