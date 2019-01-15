<?php

namespace Drupal\Tests\os_fullcalendar\ExistingSiteJavascript;

use Behat\Mink\Exception\ExpectationException;

/**
 * Tests os_fullcalendar module.
 *
 * @group vsite
 * @group functional-javascript
 * @coversDefaultClass \Drupal\os_fullcalendar\Plugin\fullcalendar\type\OsFullcalendar
 */
class EventOsFullCalendarTest extends EventExistingSiteJavascriptTestBase {

  /**
   * Tests os_fullcalendar library should load in necessary pages.
   */
  public function testOsFullCalendarLibraryLoad() {
    $this->createGroup([
      'type' => 'personal',
      'path' => [
        'alias' => '/test-alias',
      ],
    ]);

    $web_assert = $this->assertSession();

    $this->visit('/test-alias/calendar');

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
    $this->createGroup([
      'type' => 'personal',
      'path' => [
        'alias' => '/test-alias',
      ],
    ]);

    $web_assert = $this->assertSession();

    $this->visit('/test-alias/');

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
