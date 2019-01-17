<?php

namespace Drupal\Tests\os_fullcalendar\ExistingSiteJavascript;

use Behat\Mink\Exception\ExpectationException;

/**
 * Tests upcoming events calendar block.
 *
 * Ideally this should have been a test case inside
 * EventCalendarMonthlyBlockTest, but due to unknown reasons the event alias was
 * not working, and giving "page not found" error. I have already spent around
 * 4 hrs trying to identify the problem. I think it is not able to identify the
 * new alias with same pattern, after it is deleted in a previous test case.
 *
 * @group vsite
 * @group functional-javascript
 */
class EventCalendarUpcomingBlockTest extends EventExistingSiteJavascriptTestBase {

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

    $web_assert = $this->assertSession();

    $this->visit($this->aliasManager->getAliasByPath("/node/{$this->event->id()}"));

    try {
      $web_assert->pageTextContains('Upcoming Events');
      $web_assert->pageTextContains(date('F Y'));
      $web_assert->pageTextContains($future_event->label());
      $web_assert->pageTextNotContains($past_event->label());

      $this->assertTrue(TRUE);
    }
    catch (ExpectationException $e) {
      $this->fail(sprintf("Test failed: %s\nBacktrace: %s", $e->getMessage(), $e->getTraceAsString()));
    }
  }

}
