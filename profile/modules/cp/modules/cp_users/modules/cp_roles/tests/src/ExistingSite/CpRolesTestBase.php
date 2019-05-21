<?php

namespace Drupal\Tests\cp_roles\ExistingSite;

use Drupal\Tests\cp_roles\Traits\CpRolesTestTrait;
use Drupal\Tests\openscholar\ExistingSite\OsExistingSiteTestBase;

/**
 * Test base for CpRoles tests.
 */
abstract class CpRolesTestBase extends OsExistingSiteTestBase {

  use CpRolesTestTrait;

}
