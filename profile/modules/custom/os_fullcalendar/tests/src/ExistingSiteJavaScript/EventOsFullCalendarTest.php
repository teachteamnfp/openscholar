<?php

namespace Drupal\Tests\os_fullcalendar\ExistingSiteJavaScript;

use weitzman\DrupalTestTraits\ExistingSiteWebDriverTestBase;

/**
 * Tests os_fullcalendar module.
 *
 * @group vsite
 * @group functional-javascript
 * @coversDefaultClass \Drupal\os_fullcalendar\Plugin\fullcalendar\type\OsFullcalendar
 */
class EventOsFullCalendarTest extends ExistingSiteWebDriverTestBase {

  /**
   * Tests os_fullcalendar library load.
   */
  public function testOsFullCalendarLibraryLoad() {
    $this->visit('/calendar');
    $this->assertSession()->statusCodeEquals(404);
  }

}
