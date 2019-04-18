<?php

namespace Drupal\Tests\os_events\ExistingSite;

use Drupal\Component\Datetime\DateTimePlus;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * MultipleSignupHelperTest.
 *
 * @group kernel
 * @group other
 */
class MultipleSignupHelperTest extends ExistingSiteBase {

  /**
   * The created node.
   *
   * @var \Drupal\node\Entity\Node
   */
  protected $node;
  /**
   * The Registration Helper service.
   *
   * @var \Drupal\os_events\Services\RegistrationsHelper
   */
  protected $registrationHelper;

  /**
   * Create Events type node.
   */
  public function setUp() {
    parent::setUp();
    $this->registrationHelper = $this->container->get('os_events.registrations_helper');
  }

  /**
   * Tests Registration Data for positive case.
   */
  public function testRegistrationData() {

    $date = new DateTimePlus('+5 days');

    $this->node = $this->createNode([
      'type' => 'events',
      'field_location' => 'London',
      'field_signup' => TRUE,
    ]);
    $this->node->field_recurring_date->value = $date->format("Y-m-d H:i:s");
    $build['#node'] = $this->node;
    $result = $this->registrationHelper->checkRegistrationStatus($build);

    $this->assertNotNull($result['timestamp']);
    $this->assertNotNull($result['message']);
  }

  /**
   * Tests Registration Data for negative case.
   */
  public function testRegistrationStatus() {

    $this->node = $this->createNode([
      'type' => 'events',
      'field_location' => 'London',
    ]);
    $build['#node'] = $this->node;
    $result = $this->registrationHelper->checkRegistrationStatus($build);

    $this->assertEquals('Registration closed', $result['message']);
    $this->assertArrayNotHasKey('timestamp', $result);
  }

}
