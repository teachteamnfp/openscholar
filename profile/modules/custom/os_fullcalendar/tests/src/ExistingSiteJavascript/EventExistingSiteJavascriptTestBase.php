<?php

namespace Drupal\Tests\os_fullcalendar\ExistingSiteJavascript;

use Drupal\group\Entity\GroupInterface;
use Drupal\node\NodeInterface;
use weitzman\DrupalTestTraits\ExistingSiteWebDriverTestBase;

/**
 * Test base for event tests.
 */
abstract class EventExistingSiteJavascriptTestBase extends ExistingSiteWebDriverTestBase {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Test group.
   *
   * @var \Drupal\group\Entity\GroupInterface
   */
  protected $group;

  /**
   * Test event.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $event;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->entityTypeManager = $this->container->get('entity_type.manager');
    $this->config = $this->container->get('config.factory');

    $this->group = $this->createGroup([
      'type' => 'personal',
      'path' => [
        'alias' => '/test-alias',
      ],
    ]);
    $this->event = $this->createEvent([
      'title' => 'Test Event',
      'field_groups' => [
        'target_id' => $this->group->id(),
      ],
      'field_recurring_date' => [
        'value' => date("Y-m-d\TH:i:s", strtotime("today midnight")),
        'end_value' => date("Y-m-d\TH:i:s", strtotime("tomorrow midnight")),
        'rrule' => '',
        'timezone' => $this->config->get('system.date')->get('timezone.default'),
        'infinite' => FALSE,
      ],
      'status' => TRUE,
    ]);
  }

  /**
   * Creates a group.
   *
   * @param array $values
   *   (optional) The values used to create the entity.
   *
   * @return \Drupal\group\Entity\GroupInterface
   *   The created group entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  protected function createGroup(array $values = []) : GroupInterface {
    $group = $this->entityTypeManager->getStorage('group')->create($values + [
      'type' => 'default',
      'label' => $this->randomMachineName(),
    ]);
    $group->enforceIsNew();
    $group->save();

    $this->markEntityForCleanup($group);

    return $group;
  }

  /**
   * Creates an event.
   *
   * @param array $values
   *   The values used to create the event.
   *
   * @return \Drupal\node\NodeInterface
   *   The created event entity.
   */
  protected function createEvent(array $values = []) : NodeInterface {
    $event = $this->createNode($values + [
      'type' => 'events',
      'title' => $this->randomString(),
    ]);

    return $event;
  }

}
