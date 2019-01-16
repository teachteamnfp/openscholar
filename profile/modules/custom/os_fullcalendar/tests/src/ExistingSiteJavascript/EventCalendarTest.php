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
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function testEventsCalendarExists() {
    $web_assert = $this->assertSession();

    $this->group->addContent($this->event, "group_node:{$this->event->bundle()}");

    try {
      $this->visit("{$this->group->get('path')->first()->getValue()['alias']}/calendar");
      $web_assert->statusCodeEquals(200);
      $web_assert->pageTextContains($this->event->label());

      $this->assertTrue(TRUE);
    }
    catch (ExpectationException $e) {
      $this->fail(sprintf("Test failed: %s\nBacktrace: %s", $e->getMessage(), $e->getTraceAsString()));
    }
  }

  /**
   * Tests whether irrelevant events do not appear in calendar.
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function testEventsCalendarNotExists() {
    $web_assert = $this->assertSession();
    $group2 = $this->createGroup([
      'path' => [
        'alias' => '/test-alias2',
      ],
    ]);

    try {
      $this->visit("{$group2->get('path')->first()->getValue()['alias']}/calendar");
      $web_assert->statusCodeEquals(200);
      $web_assert->pageTextNotContains($this->event->label());

      $this->assertTrue(TRUE);
    }
    catch (ExpectationException $e) {
      $this->fail(sprintf("Test failed: %s\nBacktrace: %s", $e->getMessage(), $e->getTraceAsString()));
    }
  }

}
