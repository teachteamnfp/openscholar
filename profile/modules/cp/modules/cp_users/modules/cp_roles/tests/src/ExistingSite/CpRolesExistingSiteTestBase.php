<?php

namespace Drupal\Tests\cp_roles\ExistingSite;

use Drupal\Tests\cp_roles\Traits\CpRolesTestTrait;
use Drupal\Tests\openscholar\ExistingSite\OsExistingSiteTestBase;
use Drupal\vsite\Config\VsiteStorageDefinition;

/**
 * Test base for CpRoles tests.
 */
abstract class CpRolesExistingSiteTestBase extends OsExistingSiteTestBase {

  use CpRolesTestTrait;

  /**
   * {@inheritdoc}
   */
  public function tearDown() {
    // Resets the weight. Otherwise, this breaks subsequent tests.
    drupal_static_reset(VsiteStorageDefinition::ACTIVATED_VSITE_WEIGHT_KEY);

    parent::tearDown();
  }

}
