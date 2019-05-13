<?php

namespace Drupal\Tests\openscholar\ExistingSite;

use Drupal\Tests\openscholar\Traits\ExistingSiteTestTrait;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * OS kernel and functional test base.
 */
abstract class OsExistingSiteTestBase extends ExistingSiteBase {

  use ExistingSiteTestTrait;

  /**
   * Test group.
   *
   * @var \Drupal\group\Entity\GroupInterface
   */
  protected $group;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->group = $this->createGroup();
  }

}
