<?php

namespace Drupal\Tests\os_fullcalendar\ExistingSiteJavascript;

/**
 * Tests os_fullcalendar module.
 *
 * @group vsite
 * @group functional-javascript
 * @coversDefaultClass \Drupal\os_fullcalendar\Plugin\fullcalendar\type\OsFullcalendar
 */
class EventOsFullCalendarTest extends EventExistingSiteJavascriptTestBase {

  /**
   * Tests os_fullcalendar library load.
   */
  public function testOsFullCalendarLibraryLoad() {
    $group = $this->createGroup([
      'type' => 'personal',
      'path' => [
        'alias' => '/test-alias',
      ],
    ]);
    $this->visit('/test-alias/calendar');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->responseContains('os_fullcalendar.fullcalendar.js');
  }

}
