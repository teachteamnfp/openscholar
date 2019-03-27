<?php

namespace Drupal\Tests\os_fullcalendar\ExistingSite;

use Drupal\Component\Datetime\DateTimePlus;

/**
 * Tests mini calendar views.
 *
 * @group other
 * @group kernel
 */
class EventMiniCalendarViewsTest extends EventTestBase {

  /**
   * Tests monthly calendar view.
   */
  public function testMonthlyCalendarView() {
    $start = new DateTimePlus('+1 month midnight', $this->config->get('system.date')->get('timezone.default'));
    $end = new DateTimePlus('+1 day +1 month midnight', $this->config->get('system.date')->get('timezone.default'));
    /** @var \Drupal\node\NodeInterface $next_month_event */
    $next_month_event = $this->createEvent([
      'title' => 'Next Month Event',
      'field_recurring_date' => [
        'value' => $start->format("Y-m-d\TH:i:s"),
        'end_value' => $end->format("Y-m-d\TH:i:s"),
        'rrule' => '',
        'timezone' => $this->config->get('system.date')->get('timezone.default'),
        'infinite' => FALSE,
      ],
      'status' => TRUE,
    ]);
    $this->group->addContent($next_month_event, "group_node:{$next_month_event->bundle()}");

    /** @var array $result */
    $result = views_get_view_result('calendar', 'block_1');

    // Next month event should not appear.
    $this->assertCount(1, $result);
    /** @var \Drupal\views\ResultRow $item */
    foreach ($result as $item) {
      $this->assertEquals($this->event->id(), $item->nid);
    }
  }

  /**
   * Tests upcoming events calendar view.
   */
  public function testUpcomingEventsCalendarView() {
    $start = new DateTimePlus('-2 day -1 month midnight', $this->config->get('system.date')->get('timezone.default'));
    $end = new DateTimePlus('-1 day -1 month midnight', $this->config->get('system.date')->get('timezone.default'));
    /** @var \Drupal\node\NodeInterface $past_event */
    $past_event = $this->createEvent([
      'title' => 'Past Event',
      'field_recurring_date' => [
        'value' => $start->format("Y-m-d\TH:i:s"),
        'end_value' => $end->format("Y-m-d\TH:i:s"),
        'rrule' => '',
        'timezone' => $this->config->get('system.date')->get('timezone.default'),
        'infinite' => FALSE,
      ],
      'status' => TRUE,
    ]);
    $this->group->addContent($past_event, "group_node:{$past_event->bundle()}");

    $start = new DateTimePlus('tomorrow midnight', $this->config->get('system.date')->get('timezone.default'));
    $end = new DateTimePlus('2 day midnight', $this->config->get('system.date')->get('timezone.default'));
    /** @var \Drupal\node\NodeInterface $future_event */
    $future_event = $this->createEvent([
      'title' => 'Future Event',
      'field_recurring_date' => [
        'value' => $start->format("Y-m-d\TH:i:s"),
        'end_value' => $end->format("Y-m-d\TH:i:s"),
        'rrule' => '',
        'timezone' => $this->config->get('system.date')->get('timezone.default'),
        'infinite' => FALSE,
      ],
      'status' => TRUE,
    ]);
    $this->group->addContent($future_event, "group_node:{$future_event->bundle()}");

    /** @var array $result */
    $result = views_get_view_result('calendar', 'block_2');

    $this->assertCount(1, $result);
    /** @var \Drupal\views\ResultRow $item */
    foreach ($result as $item) {
      $this->assertEquals($future_event->id(), $item->nid);
    }
  }

}
