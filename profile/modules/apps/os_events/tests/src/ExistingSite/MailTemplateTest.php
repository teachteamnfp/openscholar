<?php

namespace Drupal\Tests\os_events\ExistingSite;

use Drupal\rng\Entity\Rule;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * MailTemplateTest.
 *
 * @group kernel
 * @group others
 */
class MailTemplateTest extends ExistingSiteBase {

  /**
   * Entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeInterface
   */
  protected $entityTypeManager;

  /**
   * The created node.
   *
   * @var \Drupal\node\Entity\Node
   */
  protected $node;

  /**
   * Create Events type node.
   */
  public function setUp() {
    parent::setUp();

    $this->node = $this->createNode([
      'type' => 'events',
      'field_location' => 'London',
      'field_signup' => TRUE,
    ]);
    $this->entityTypeManager = $this->container->get('entity_type.manager');
  }

  /**
   * Tests default template creation.
   */
  public function testDefaultTemplateCreation() {

    $entityStorage = $this->entityTypeManager->getStorage('courier_template_collection');
    $result = $entityStorage->getQuery()
      ->condition('owner.target_id', $this->node->id())
      ->condition('owner.target_type', 'node')
      ->execute();
    $this->assertNotNull($result);
  }

  /**
   * Tests Rule creation.
   */
  public function testRuleCreation() {
    $entityStorage = $this->entityTypeManager->getStorage('rng_rule');
    $result = $entityStorage->getQuery()
      ->condition('event.target_id', $this->node->id())
      ->condition('event.target_type', 'node')
      ->condition('trigger_id', 'entity:registration:new')
      ->condition('status', '1')
      ->execute();
    $this->assertNotNull($result);
  }

  /**
   * Tests when a new template is created it is active by default.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testRuleIsActive() {
    $rule = Rule::create([
      'event' => ['entity' => $this->node],
      'trigger_id' => 'entity:registration:new',
    ]);
    $rule->save();
    $this->assertTrue($rule->isActive());
    $this->markEntityForCleanup($rule);
  }

}
