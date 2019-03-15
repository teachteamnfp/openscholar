<?php

namespace Drupal\Tests\vsite\ExistingSite;

use Drupal\group\Entity\Group;

/**
 * VsiteContextManagerTest.
 *
 * @group vsite
 * @group kernel
 */
class VsiteContextManagerTest extends VsiteExistingSiteTestBase {

  /**
   * Vsite context manager.
   *
   * @var \Drupal\vsite\Plugin\VsiteContextManagerInterface
   */
  protected $vsiteContextManager;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->vsiteContextManager = $this->container->get('vsite.context_manager');
  }

  /**
   * Tests activateVsite.
   *
   * @covers \Drupal\vsite\Plugin\VsiteContextManager::activateVsite
   */
  public function testActivateVsite() {
    // Negative test.
    $unsaved_group = Group::create([
      'type' => 'personal',
    ]);
    $this->vsiteContextManager->activateVsite($unsaved_group);

    $this->assertNull($this->vsiteContextManager->getActiveVsite());

    // Positive test.
    $saved_group = $this->createGroup([
      'type' => 'personal',
    ]);
    $this->vsiteContextManager->activateVsite($saved_group);

    $this->assertEquals($saved_group->id(), $this->vsiteContextManager->getActiveVsite()->id());
  }

}
