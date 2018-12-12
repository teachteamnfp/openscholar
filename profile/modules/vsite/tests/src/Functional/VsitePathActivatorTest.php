<?php

namespace Drupal\Tests\vsite\Functional;

use Drupal\Tests\group\Functional\GroupBrowserTestBase;

/**
 * Tests VsitePathActivator.
 *
 * @group functional
 * @group vsite
 * @coversDefaultClass \Drupal\vsite\Plugin\VsitePathActivator
 */
class VsitePathActivatorTest extends GroupBrowserTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'purl',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    /** @var \Drupal\Core\State\StateInterface $state */
    $state = $this->container->get('state');
    $state->set('system.maintenance_mode', 0);
    $state->resetCache();
    // TODO: Remove this once debugging is completed.
    // $this->htmlOutputEnabled = TRUE;
    // $this->htmlOutput();
    $this->drupalLogin($this->groupCreator);
  }

  /**
   * Tests modifier matched event.
   */
  public function testModifierMatched() {
    $group = $this->createGroup([
      'type' => 'personal',
      'path' => [
        'alias' => 'test-alias',
      ],
    ]);

    $this->drupalGet('/test-alias');
  }

}
