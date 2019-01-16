<?php

namespace Drupal\Tests\os_fullcalendar\ExistingSiteJavascript;

use Behat\Mink\Exception\ExpectationException;

/**
 * Tests calendar blocks.
 *
 * @group vsite
 * @group functional-javascript
 */
class EventCalendarBlocksTest extends EventExistingSiteJavascriptTestBase {

  /**
   * Tests monthly block.
   */
  public function testMonthlyBlock() {
    $web_assert = $this->assertSession();

    $this->visit($this->aliasManager->getAliasByPath("/node/{$this->event->id()}"));

    $this->getSession()->resizeWindow(1440, 900, 'current');
    $this->getSession()->executeScript("window.scrollBy(0,1000)");
    file_put_contents('public://screenshot-1.jpg', $this->getSession()->getScreenshot());
    try {
      $web_assert->statusCodeEquals(200);
      $web_assert->pageTextContains('Monthly Calendar');
      $web_assert->pageTextContains(date('F Y'));
      $web_assert->pageTextContains($this->event->label());

      $this->assertTrue(TRUE);
    }
    catch (ExpectationException $e) {
      $this->fail(sprintf("Test failed: %s\nBacktrace: %s", $e->getMessage(), $e->getTraceAsString()));
    }
  }

  /**
   * Tests upcoming events block.
   */
  public function testUpcomingEventsBlock() {
    $web_assert = $this->assertSession();

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
    $this->group->addContent($past_event, "group_node:{$past_event->bundle()}");

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
    $this->group->addContent($future_event, "group_node:{$future_event->bundle()}");

    $this->visit($this->aliasManager->getAliasByPath("/node/{$future_event->id()}"));

    $this->getSession()->resizeWindow(1440, 900, 'current');
    $this->getSession()->executeScript("window.scrollBy(0,1000)");
    file_put_contents('public://screenshot-2.jpg', $this->getSession()->getScreenshot());
    try {
      $web_assert->statusCodeEquals(200);
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
