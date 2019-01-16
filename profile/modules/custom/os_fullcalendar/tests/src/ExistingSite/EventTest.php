<?php

namespace Drupal\Tests\os_fullcalendar\ExistingSite;

/**
 * Event tests.
 *
 * @group vsite
 * @group kernel
 */
class EventTest extends EventTestBase {

  /**
   * Tests event alias.
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function testEventAlias() {
    /** @var \Drupal\Core\Path\AliasManagerInterface $alias_manager */
    $alias_manager = $this->container->get('path.alias_manager');

    /** @var \Drupal\node\NodeInterface $event */
    $event = $this->createEvent([
      'title' => 'Test Unique Event',
      'field_groups' => [
        'target_id' => $this->group->id(),
      ],
      'status' => TRUE,
    ]);
    $this->group->addContent($event, "group_node:{$event->bundle()}");

    $this->assertEquals($alias_manager->getAliasByPath("/node/{$event->id()}"), "{$this->group->get('path')->first()->getValue()['alias']}/event/test-unique-event");
  }

}
