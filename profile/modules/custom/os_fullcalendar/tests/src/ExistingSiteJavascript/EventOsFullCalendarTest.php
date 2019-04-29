<?php

namespace Drupal\Tests\os_fullcalendar\ExistingSiteJavascript;

use Behat\Mink\Exception\ExpectationException;

/**
 * Tests os_fullcalendar module.
 *
 * @group functional-javascript
 * @group events
 */
class EventOsFullCalendarTest extends EventExistingSiteJavascriptTestBase {

  /**
   * Tests os_fullcalendar library should load in necessary pages.
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function testOsFullCalendarLibraryLoad() {
    $web_assert = $this->assertSession();

    $this->visit("{$this->group->get('path')->first()->getValue()['alias']}/calendar");

    try {
      $web_assert->statusCodeEquals(200);
      $web_assert->responseContains('os_fullcalendar.fullcalendar.js');
      $this->assertTrue(TRUE);
    }
    catch (ExpectationException $e) {
      $this->fail(sprintf("Test failed: %s\nBacktrace: %s", $e->getMessage(), $e->getTraceAsString()));
    }
  }

  /**
   * Tests os_fullcalendar library should not load in unnecessary pages.
   */
  public function testOsFullCalendarLibraryNoLoad() {
    $web_assert = $this->assertSession();

    $this->visit("{$this->group->get('path')->first()->getValue()['alias']}/");

    try {
      $web_assert->statusCodeEquals(200);
      $web_assert->responseNotContains('os_fullcalendar.fullcalendar.js');
      $this->assertTrue(TRUE);
    }
    catch (ExpectationException $e) {
      $this->fail(sprintf("Test failed: %s\nBacktrace: %s", $e->getMessage(), $e->getTraceAsString()));
    }
  }

}
