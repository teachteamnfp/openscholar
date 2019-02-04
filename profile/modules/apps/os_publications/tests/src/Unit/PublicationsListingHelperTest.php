<?php

namespace Drupal\Tests\os_publications\ExistingSite;

use Drupal\os_publications\PublicationsListingHelper;
use Drupal\Tests\UnitTestCase;

/**
 * LabelHelperTest.
 *
 * @group vsite
 * @group unit
 * @coversDefaultClass \Drupal\os_publications\PublicationsListingHelper
 */
class PublicationsListingHelperTest extends UnitTestCase {

  /**
   * LabelHelper.
   *
   * @var \Drupal\os_publications\PublicationsListingHelperInterface
   */
  protected $labelHelper;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->labelHelper = new PublicationsListingHelper();
  }

  /**
   * Tests convertToPublicationsListingLabel.
   *
   * @covers ::convertLabel
   */
  public function testConvertToPublicationsListingLabel() {
    $this->assertEquals('T', $this->labelHelper->convertLabel('Talk Talk'));
    $this->assertEquals('L', $this->labelHelper->convertLabel('The Lord of the Rings'));
    $this->assertEquals('U', $this->labelHelper->convertLabel('From up on Poppy Hill'));
  }

  /**
   * Tests convertToPublicationsListingAuthorName.
   *
   * @covers ::convertAuthorName
   */
  public function testConvertToPublicationsListingAuthorName() {
    $this->assertEquals('H', $this->labelHelper->convertAuthorName('hollis'));
  }

}
