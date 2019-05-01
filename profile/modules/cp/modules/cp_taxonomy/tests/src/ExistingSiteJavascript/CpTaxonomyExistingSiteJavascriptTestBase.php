<?php

namespace Drupal\Tests\cp_taxonomy\ExistingSiteJavascript;

use Drupal\group\Entity\GroupRole;
use Drupal\group\Entity\GroupType;
use Drupal\Tests\openscholar\ExistingSiteJavascript\OsExistingSiteJavascriptTestBase;

/**
 * Test base for cp_taxonomy js tests.
 */
abstract class CpTaxonomyExistingSiteJavascriptTestBase extends OsExistingSiteJavascriptTestBase {

  protected const PERMISSIONS = [
    'create group_node:taxonomy_test_1 entity',
    'delete any group_node:taxonomy_test_1 entity',
    'delete own group_node:taxonomy_test_1 entity',
    'update any group_node:taxonomy_test_1 entity',
    'update own group_node:taxonomy_test_1 entity',
    'view group_node:taxonomy_test_1 entity',
    'view unpublished group_node:taxonomy_test_1 entity',
    'create group_node:taxonomy_test_1 content',
    'delete any group_node:taxonomy_test_1 content',
    'delete own group_node:taxonomy_test_1 content',
    'update any group_node:taxonomy_test_1 content',
    'update own group_node:taxonomy_test_1 content',
    'view group_node:taxonomy_test_1 content',
    'create group_node:taxonomy_test_2 entity',
    'delete any group_node:taxonomy_test_2 entity',
    'delete own group_node:taxonomy_test_2 entity',
    'update any group_node:taxonomy_test_2 entity',
    'update own group_node:taxonomy_test_2 entity',
    'view group_node:taxonomy_test_2 entity',
    'view unpublished group_node:taxonomy_test_2 entity',
    'create group_node:taxonomy_test_2 content',
    'delete any group_node:taxonomy_test_2 content',
    'delete own group_node:taxonomy_test_2 content',
    'update any group_node:taxonomy_test_2 content',
    'update own group_node:taxonomy_test_2 content',
    'view group_node:taxonomy_test_2 content',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $personal_group_type = GroupType::load('personal');
    $personal_group_type->installContentPlugin('group_node:taxonomy_test_1')
      ->installContentPlugin('group_node:taxonomy_test_2')
      ->save();

    $group_admin_role = GroupRole::load('personal-administrator');
    $group_admin_role->grantPermissions(self::PERMISSIONS)->save();
  }

  /**
   * {@inheritdoc}
   */
  public function tearDown() {
    $group_admin_role = GroupRole::load('personal-administrator');
    $group_admin_role->revokePermissions(self::PERMISSIONS)->save();

    $personal_group_type = GroupType::load('personal');
    $personal_group_type->uninstallContentPlugin('group_node:taxonomy_test_1')
      ->uninstallContentPlugin('group_node:taxonomy_test_2')
      ->save();

    parent::tearDown();
  }

}
