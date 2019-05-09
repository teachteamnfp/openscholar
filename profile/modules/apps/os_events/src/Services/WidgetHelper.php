<?php

namespace Drupal\os_events\Services;

use DateInterval;
use DateTime;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\date_recur\DateRecurHelper;
use Drupal\date_recur_modular\DateRecurModularWidgetOptions;
use Drupal\os_events\WidgetHelperInterface;

/**
 * Class WidgetHelper.
 *
 * @package Drupal\os_events\Services
 */
class WidgetHelper implements WidgetHelperInterface {

  /**
   * The format to save until limit in.
   */
  const FORMAT = 'Ymd\THis\Z';

  /**
   * {@inheritdoc}
   */
  public function alterRrule(string $rrule, array $fieldRecurringDate): string {
    $count = '';
    $untilInput = '';
    $endsMode = $fieldRecurringDate['ends_mode'];
    $startDate = clone $fieldRecurringDate['start'];

    /** @var \Drupal\Core\Datetime\DrupalDateTime|array|null $endsDate */
    $endsDate = array_shift($fieldRecurringDate['ends_date']);

    if (stripos($rrule, 'YEARLY')) {
      // If yearly event extend upto 5 years by default.
      $until = $startDate->add(new DateInterval('P5Y'));
    }
    else {
      // Default 1 year limit.
      $until = $startDate->add(new DateInterval('P1Y'));
    }
    $untilDefault = $until->format(WidgetHelper::FORMAT);

    // Ends mode.
    if ($endsMode === DateRecurModularWidgetOptions::ENDS_MODE_OCCURRENCES) {
      $count = (int) $fieldRecurringDate['ends_count'];
    }
    elseif ($endsMode === DateRecurModularWidgetOptions::ENDS_MODE_ON_DATE && $endsDate instanceof DrupalDateTime) {
      $adjusted = clone $endsDate;
      $untilInput = $adjusted->format(WidgetHelper::FORMAT);
    }

    // Append user input to RRule.
    if ($count || $untilInput) {
      $rrule .= $untilInput ? ";UNTIL=$untilInput" : '';
      $rrule .= $count ? ";COUNT=$count" : '';
    }
    // Set Defaults if no user input is there.
    else {
      // If daily event set count 50 instead of until.
      if (stripos($rrule, 'DAILY')) {
        $rrule .= ";COUNT=50";
      }
      else {
        $rrule .= ";UNTIL=$untilDefault";
      }
    }
    return $rrule;
  }

  /**
   * {@inheritdoc}
   */
  public function getRecurrenceOptions(string $rrule, DateTime $startDate) : string {

    try {
      $helper = DateRecurHelper::create($rrule, $startDate);
      /** @var \Drupal\date_recur\DateRecurRuleInterface[] $rules */
      $rules = $helper->getRules();
      $rule = reset($rules);
      if (!isset($rule)) {
        return 'custom';
      }
    }
    catch (\Exception $e) {
      return 'custom';
    }

    $parts = array_filter($rule->getParts());
    $frequency = $rule->getFrequency();
    $byParts = array_filter($parts, function ($value, $key): bool {
      return strpos($key, 'BY', 0) === 0;
    }, \ARRAY_FILTER_USE_BOTH);

    $byPartCount = count($byParts);
    $weekdaysKeys = ['SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA'];
    $byDay = explode(',', $byParts['BYDAY'] ?? '');
    $byDay = array_unique(array_intersect($weekdaysKeys, $byDay));
    $byDayStr = implode(',', $byDay);

    $byMonth = array_unique(explode(',', $byParts['BYMONTH'] ?? ''));
    sort($byMonth);
    $byMonthDay = array_unique(explode(',', $byParts['BYMONTHDAY'] ?? ''));
    sort($byMonthDay);
    $bySetPos = array_unique(explode(',', $byParts['BYSETPOS'] ?? ''));
    sort($bySetPos);

    if ($byPartCount === 0 && $frequency === 'DAILY') {
      return 'daily';
    }
    elseif ($frequency === 'WEEKLY' && $byDayStr === 'MO,TU,WE,TH,FR' && $byPartCount === 1) {
      return 'weekdayly';
    }
    elseif ($frequency === 'WEEKLY' && $byPartCount === 1 && count($byDay) === 1) {
      return 'weekly_oneday';
    }
    elseif ($frequency === 'MONTHLY' && $byPartCount === 2 && count($bySetPos) === 1 && count($byDay) === 1) {
      return 'monthly_th_weekday';
    }
    elseif ($frequency === 'YEARLY' && $byPartCount === 2 && count($byMonth) === 1 && count($byMonthDay) === 1) {
      return 'yearly_monthday';
    }

    return 'custom';
  }

}
