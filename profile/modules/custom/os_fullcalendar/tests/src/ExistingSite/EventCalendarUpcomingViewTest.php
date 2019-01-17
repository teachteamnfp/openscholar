<?php

namespace Drupal\Tests\os_fullcalendar\ExistingSite;

/**
 * Tests upcoming events calendar view.
 *
 * @group vsite
 * @group kernel
 */
class EventCalendarUpcomingViewTest extends EventTestBase {

  /**
   * Tests the block.
   */
  public function testBlock() {
    /** @var \Drupal\node\NodeInterface $past_event */
    $past_event = $this->createEvent([
      'title' => 'Past Event',
      'field_groups' => [
        'target_id' => $this->group->id(),
      ],
      'field_recurring_date' => [
        'value' => date("Y-m-d\TH:i:s", strtotime("-2 day -1 month midnight")),
        'end_value' => date("Y-m-d\TH:i:s", strtotime("-1 day -1 month midnight")),
        'rrule' => '',
        'timezone' => $this->config->get('system.date')->get('timezone.default'),
        'infinite' => FALSE,
      ],
      'status' => TRUE,
    ]);

    /** @var \Drupal\node\NodeInterface $future_event */
    $future_event = $this->createEvent([
      'title' => 'Future Event',
      'field_groups' => [
        'target_id' => $this->group->id(),
      ],
      'field_recurring_date' => [
        'value' => date("Y-m-d\TH:i:s", strtotime("1 day midnight")),
        'end_value' => date("Y-m-d\TH:i:s", strtotime("2 day midnight")),
        'rrule' => '',
        'timezone' => $this->config->get('system.date')->get('timezone.default'),
        'infinite' => FALSE,
      ],
      'status' => TRUE,
    ]);

    /** @var array $result */
    $result = views_get_view_result('calendar', 'block_2', $this->group->id());

    $this->assertCount(1, $result);
    /** @var \Drupal\views\ResultRow $item */
    foreach ($result as $item) {
      $this->assertEquals($future_event->id(), $item->nid);
    }
  }

}
