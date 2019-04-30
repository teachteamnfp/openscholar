<?php

namespace Drupal\Tests\os_events\ExistingSite;

use DateTime;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * WidgetHelperTest.
 *
 * @group kernel
 * @group other
 */
class WidgetHelperTest extends ExistingSiteBase {

  /**
   * The created node.
   *
   * @var \Drupal\node\Entity\Node
   */
  protected $node;
  /**
   * The Widget Helper service.
   *
   * @var \Drupal\os_events\Services\WidgetHelper
   */
  protected $widgetHelper;

  /**
   * Create Events type node.
   */
  public function setUp() {
    parent::setUp();
    $this->widgetHelper = $this->container->get('os_events.widget_helper');
  }

  /**
   * Tests alter Rrule.
   *
   * @throws \Exception
   */
  public function testAlterRrule() {

    $dateTime = new DateTime('+5 days');

    $rrule = 'FREQ=DAILY';
    $fieldRecurringDate['ends_mode'] = "count";
    $fieldRecurringDate['start'] = $dateTime;
    $fieldRecurringDate['ends_count'] = 5;
    $fieldRecurringDate['ends_date'] = NULL;

    $result = $this->widgetHelper->alterRrule($rrule, $fieldRecurringDate);

    $this->assertEquals('FREQ=DAILY;COUNT=5', $result);
  }

  /**
   * Tests get recurrence options.
   */
  public function testRecurrenceOptions() {

    $dateTime = new DateTime('+5 days');
    $rrule = 'FREQ=YEARLY;BYMONTH=4;BYMONTHDAY=5;COUNT=5';
    $result = $this->widgetHelper->getRecurrenceOptions($rrule, $dateTime);
    $this->assertEquals('yearly_monthday', $result);
  }

}
