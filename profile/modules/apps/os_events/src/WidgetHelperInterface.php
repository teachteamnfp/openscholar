<?php

namespace Drupal\os_events;

use DateTime;

/**
 * Widget Helper interface.
 */
interface WidgetHelperInterface {

  /**
   * Alters the rrule and returns the result.
   *
   * @param string $rrule
   *   The rrule uptil now.
   * @param array $field_recurring_date
   *   The recurring date field values.
   *
   * @return string
   *   The altered Rrule.
   *
   * @throws \Exception
   */
  public function alterRrule(string $rrule, array $field_recurring_date): string;

  /**
   * Guesses current Recurrence option.
   *
   * @param string $rrule
   *   The rrule from storage.
   * @param \DateTime $startDate
   *   The Start Date.
   *
   * @return string
   *   The option's value.
   */
  public function getRecurrenceOptions(string $rrule, DateTime $startDate) : string;

}
