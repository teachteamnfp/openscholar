<?php

namespace Drupal\Tests\os_fullcalendar\ExistingSiteJavascript;

use Behat\Mink\Exception\ExpectationException;

/**
 * Tests monthly calendar block.
 *
 * @group vsite
 * @group functional-javascript
 */
class EventCalendarMonthlyBlockTest extends EventExistingSiteJavascriptTestBase {

  /**
   * Tests the block.
   */
  public function testBlock() {
    $web_assert = $this->assertSession();

    $this->visit($this->aliasManager->getAliasByPath("/node/{$this->event->id()}"));

    try {
      $web_assert->pageTextContains('Monthly Calendar');
      $web_assert->pageTextContains(date('F Y'));
      $web_assert->pageTextContains($this->event->label());

      $this->assertTrue(TRUE);
    }
    catch (ExpectationException $e) {
      $this->fail(sprintf("Test failed: %s\nBacktrace: %s", $e->getMessage(), $e->getTraceAsString()));
    }
  }

}
