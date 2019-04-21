<?php

namespace Drupal\os_events;

use Drupal\Core\Entity\EntityInterface;

/**
 * Adds and enables a default signup email template.
 */
interface MailNotificationsInterface {

  /**
   * Sends Registration Confirmation Mail.
   *
   * @param \Drupal\Core\Entity\EntityInterface $registrant
   *   The registrant entity.
   */
  public function sendConfirmationEmail(EntityInterface $registrant);

  /**
   * Sends Event Date/Location update notification.
   *
   * @param \Drupal\Core\Entity\EntityInterface $event
   *   Event entity.
   */
  public function sendUpdateNotificationEmail(EntityInterface $event);

  /**
   * Sends Event cancellation notification.
   *
   * @param \Drupal\Core\Entity\EntityInterface $event
   *   Event entity.
   */
  public function sendCancelNotificationEmail(EntityInterface $event);

  /**
   * Sends Event full notification.
   *
   * @param \Drupal\Core\Entity\EntityInterface $event
   *   Event entity.
   */
  public function sendEventFullEmail(EntityInterface $event);

  /**
   * Sets up a email reminder schedule for an entity in context.
   *
   * @param array $values
   *   Form values of the entity rng settings form.
   * @param \Drupal\Core\Entity\EntityInterface $event
   *   The event node itself.
   */
  public function setUpReminderEmail(array $values, EntityInterface $event);

}
