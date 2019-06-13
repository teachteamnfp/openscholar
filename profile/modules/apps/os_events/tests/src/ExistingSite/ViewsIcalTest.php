<?php

namespace Drupal\Tests\os_events\ExistingSite;

/**
 * Tests views_ical 2.0.
 *
 * @coversDefaultClass \Drupal\views_ical\ViewsIcalHelper
 * @group kernel
 * @group other
 */
class ViewsIcalTest extends EventsTestBase {

  /**
   * Test user.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $user;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->user = $this->createUser();
  }

  /**
   * @covers ::createDefaultEvent
   * @covers ::addDateRecurEvent
   */
  public function testAddDateRecurEvent(): void {
    /** @var \Drupal\views_ical\ViewsIcalHelperInterface $views_ical_helper */
    $views_ical_helper = $this->container->get('views_ical.helper');
    $event = $this->createEvent([
      'title' => 'Test Event',
      'field_recurring_date' => [
        'value' => '2019-01-29T10:00:00',
        'end_value' => '2019-01-29T11:00:00',
        'rrule' => 'FREQ=DAILY;INTERVAL=1;COUNT=7',
        'timezone' => $this->user->getTimeZone(),
        'infinite' => FALSE,
      ],
      'status' => TRUE,
      'field_location' => [
        'value' => 'Mars',
      ],
      'body' => [
        'value' => 'Inter-Galactic Event',
      ],
    ]);

    // Test with all field mapping.
    $events = [];
    $field_mapping = [
      'date_field' => 'field_recurring_date',
      'summary_field' => 'title',
      'location_field' => 'field_location',
      'description_field' => 'body',
    ];
    $views_ical_helper->addDateRecurEvent($events, $event, new \DateTimeZone($this->user->getTimeZone()), $field_mapping);

    $this->assertCount(7, $events);

    foreach ($events as $item) {
      $ical = $item->render();

      $this->assertNotNull($item->getDtStart());
      $this->assertNotNull($item->getDtEnd());
      $this->assertTrue($item->getUseTimezone());

      $this->assertContains('SUMMARY:Test Event', $ical);
      $this->assertContains('LOCATION:Mars', $ical);
      $this->assertContains('DESCRIPTION:Inter-Galactic Event', $ical);
    }

    // Tests without location field mapping.
    $events = [];
    $field_mapping = [
      'date_field' => 'field_recurring_date',
      'summary_field' => 'title',
      'description_field' => 'body',
    ];
    $views_ical_helper->addDateRecurEvent($events, $event, new \DateTimeZone($this->user->getTimeZone()), $field_mapping);

    $this->assertCount(7, $events);

    foreach ($events as $item) {
      $ical = $item->render();

      $this->assertNotNull($item->getDtStart());
      $this->assertNotNull($item->getDtEnd());
      $this->assertTrue($item->getUseTimezone());

      $this->assertContains('SUMMARY:Test Event', $ical);
      $this->assertNotContains('LOCATION:Mars', $ical);
      $this->assertContains('DESCRIPTION:Inter-Galactic Event', $ical);
    }

    // Tests without description field mapping.
    $events = [];
    $field_mapping = [
      'date_field' => 'field_recurring_date',
      'summary_field' => 'title',
    ];
    $views_ical_helper->addDateRecurEvent($events, $event, new \DateTimeZone($this->user->getTimeZone()), $field_mapping);

    $this->assertCount(7, $events);

    foreach ($events as $item) {
      $ical = $item->render();

      $this->assertNotNull($item->getDtStart());
      $this->assertNotNull($item->getDtEnd());
      $this->assertTrue($item->getUseTimezone());

      $this->assertContains('SUMMARY:Test Event', $ical);
      $this->assertNotContains('LOCATION:Mars', $ical);
      $this->assertNotContains('DESCRIPTION:Inter-Galactic Event', $ical);
    }

    // Tests without summary field mapping.
    $events = [];
    $field_mapping = [
      'date_field' => 'field_recurring_date',
    ];
    $views_ical_helper->addDateRecurEvent($events, $event, new \DateTimeZone($this->user->getTimeZone()), $field_mapping);

    $this->assertCount(7, $events);

    foreach ($events as $item) {
      $ical = $item->render();

      $this->assertNotNull($item->getDtStart());
      $this->assertNotNull($item->getDtEnd());
      $this->assertTrue($item->getUseTimezone());

      $this->assertNotContains('SUMMARY:Test Event', $ical);
      $this->assertNotContains('LOCATION:Mars', $ical);
      $this->assertNotContains('DESCRIPTION:Inter-Galactic Event', $ical);
    }
  }

  /**
   * @covers ::addEvent
   * @throws \Exception
   */
  public function testAddEvent(): void {
    /** @var \Drupal\views_ical\ViewsIcalHelperInterface $views_ical_helper */
    $views_ical_helper = $this->container->get('views_ical.helper');
    $field_mapping = [
      'date_field' => 'field_open_date',
      'summary_field' => 'title',
      'location_field' => 'field_location',
      'description_field' => 'body',
    ];

    // Test without end date.
    $event = $this->createEvent([
      'title' => 'Test Event',
      'field_open_date' => [
        'value' => '2019-01-29T10:00:00',
      ],
      'status' => TRUE,
      'field_location' => [
        'value' => 'Pluto',
      ],
      'body' => [
        'value' => 'Inter-Galactic Event in a non-planet',
      ],
    ]);

    $events = [];
    $views_ical_helper->addEvent($events, $event, new \DateTimeZone($this->user->getTimeZone()), $field_mapping);

    $this->assertCount(1, $events);

    foreach ($events as $item) {
      $ical = $item->render();

      $this->assertNotNull($item->getDtStart());
      $this->assertNull($item->getDtEnd());
      $this->assertTrue($item->getUseTimezone());

      $this->assertContains('SUMMARY:Test Event', $ical);
      $this->assertContains('LOCATION:Pluto', $ical);
      $this->assertContains('DESCRIPTION:Inter-Galactic Event in a non-planet', $ical);
    }

    // Test with end date.
    $event = $this->createEvent([
      'title' => 'Test Event',
      'field_open_date' => [
        'value' => '2019-01-29T10:00:00',
        'end_value' => '2019-01-29T11:00:00',
      ],
      'status' => TRUE,
      'field_location' => [
        'value' => 'Pluto',
      ],
      'body' => [
        'value' => 'Inter-Galactic Event in a non-planet',
      ],
    ]);

    $events = [];
    $views_ical_helper->addEvent($events, $event, new \DateTimeZone($this->user->getTimeZone()), $field_mapping);

    $this->assertCount(1, $events);

    foreach ($events as $item) {
      $ical = $item->render();

      $this->assertNotNull($item->getDtStart());
      $this->assertNotNull($item->getDtEnd());
      $this->assertTrue($item->getUseTimezone());

      $this->assertContains('SUMMARY:Test Event', $ical);
      $this->assertContains('LOCATION:Pluto', $ical);
      $this->assertContains('DESCRIPTION:Inter-Galactic Event in a non-planet', $ical);
    }
  }

}
