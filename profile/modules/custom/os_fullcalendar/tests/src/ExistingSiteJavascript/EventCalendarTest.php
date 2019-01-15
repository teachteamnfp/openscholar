<?php

namespace Drupal\Tests\os_fullcalendar\ExistingSiteJavascript;

use Behat\Mink\Exception\ExpectationException;

/**
 * Tests event calendar.
 */
class EventCalendarTest extends EventExistingSiteJavascriptTestBase {

  /**
   * Tests whether relevant events are appearing on calendar.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function testEventsCalendarExists() {
    /** @var \Drupal\group\Entity\GroupInterface $group */
    $group = $this->createGroup([
      'type' => 'personal',
      'path' => [
        'alias' => '/test-alias',
      ],
    ]);

    $web_assert = $this->assertSession();

    /** @var \Drupal\node\NodeInterface $event */
    $event = $this->createEvent([
      'title' => 'Test Event',
      'field_groups' => [
        'target_id' => $group->id(),
      ],
      'field_recurring_date' => [
        'value' => date("Y-m-d\TH:i:s", strtotime("today midnight")),
        'end_value' => date("Y-m-d\TH:i:s", strtotime("tomorrow midnight")),
        'rrule' => '',
        'timezone' => \Drupal::config('system.date')->get('timezone.default'),
        'infinite' => FALSE,
      ],
      'status' => TRUE,
    ]);

    $group->addContent($event, 'group_node:events');

    try {
      $this->visit('/test-alias/calendar');
      $web_assert->statusCodeEquals(200);
      $web_assert->pageTextContains('Test Event');

      $this->assertTrue(TRUE);
    }
    catch (ExpectationException $e) {
      $this->fail(sprintf("Test failed: %s\nBacktrace: %s", $e->getMessage(), $e->getTraceAsString()));
    }
  }

  /**
   * Tests whether irrelevant events do not appear in calendar.
   */
  public function testEventsCalendarNotExists() {
    // TODO: Implement.
  }

}
