<?php

namespace Drupal\Tests\os_publications\ExistingSite;

use Drupal\os_publications\LabelHelper;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * LabelHelperTest.
 *
 * @group vsite
 * @group unit
 * @coversDefaultClass \Drupal\os_publications\LabelHelper
 */
class LabelHelperTest extends ExistingSiteBase {

  /**
   * LabelHelper.
   *
   * @var \Drupal\os_publications\LabelHelperInterface
   */
  protected $labelHelper;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->labelHelper = new LabelHelper();
  }

  /**
   * Tests convertToPublicationsListingLabel.
   *
   * @covers ::convertToPublicationsListingLabel
   */
  public function testConvertToPublicationsListingLabel() {
    $this->assertEquals('T', $this->labelHelper->convertToPublicationsListingLabel('Talk Talk'));
    $this->assertEquals('L', $this->labelHelper->convertToPublicationsListingLabel('The Lord of the Rings'));
    $this->assertEquals('U', $this->labelHelper->convertToPublicationsListingLabel('From up on Poppy Hill'));
  }

}
