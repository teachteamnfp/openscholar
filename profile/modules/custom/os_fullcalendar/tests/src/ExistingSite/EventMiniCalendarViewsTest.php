<?php

namespace Drupal\Tests\os_fullcalendar\ExistingSite;

use Drupal\Component\Datetime\DateTimePlus;
use Drupal\views\Views;

/**
 * Tests mini calendar views.
 *
 * @group vsite
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
    $view = Views::getView('calendar');
    $view->setDisplay('block_1');
    $view->preExecute();
    $view->execute();
    file_put_contents('public://query.txt', $view->query->query());

    $datetime = new DateTimePlus('now');
    file_put_contents('public://now.txt', $datetime->format("Y-m-d\TH:i:s"));
    file_put_contents('public://timezone.txt', $datetime->getTimezone());
    file_put_contents('public://now-php.txt', $datetime->getPhpDateTime());
    file_put_contents('public://offset.txt', $datetime->getOffset());
    file_put_contents('public://errors.txt', print_r($datetime->getErrors(), TRUE));

    $datetime = new DateTimePlus('now', new \DateTimeZone('America/Anguilla'));
    file_put_contents('public://now-anguilla.txt', $datetime->format("Y-m-d\TH:i:s"));

    $datetime = new DateTimePlus($next_month_event->field_recurring_date->first()->getValue()['value']);
    file_put_contents("public://{$next_month_event->label()}-utc.txt", $datetime->format("Y-m-d\TH:i:s"));

    $datetime = new DateTimePlus($this->event->field_recurring_date->first()->getValue()['value']);
    file_put_contents("public://{$this->event->label()}-utc.txt", $datetime->format("Y-m-d\TH:i:s"));

    $connection = \Drupal::database();
    $query = $connection->query("SELECT * from node_field_data");
    file_put_contents("public://node_field_data.txt", print_r($query->fetchAll(), TRUE));
    $query = $connection->query("SELECT * FROM group_content_field_data");
    file_put_contents("public://group_content_field_data.txt", print_r($query->fetchAll(), TRUE));
    $query = $connection->query("SELECT * FROM node__field_recurring_date");
    file_put_contents("public://node__field_recurring_date.txt", print_r($query->fetchAll(), TRUE));

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
