<?php

namespace Drupal\Tests\cp_taxonomy\ExistingSiteJavascript;

use Drupal\group\Entity\GroupRole;
use Drupal\group\Entity\GroupType;
use Drupal\Tests\openscholar\ExistingSiteJavascript\OsExistingSiteJavascriptTestBase;

/**
 * Test base for cp_taxonomy js tests.
 */
abstract class CpTaxonomyExistingSiteJavascriptTestBase extends OsExistingSiteJavascriptTestBase {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Editable cp_taxonomy.settings.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * Vsite Context Manager.
   *
   * @var \Drupal\vsite\Plugin\VsiteContextManagerInterface
   */
  protected $vsiteContextManager;

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
    if (!$personal_group_type->hasContentPlugin('group_node:taxonomy_test_1')) {
      $personal_group_type->installContentPlugin('group_node:taxonomy_test_1');
      $personal_group_type->save();
    }

    if (!$personal_group_type->hasContentPlugin('group_node:taxonomy_test_2')) {
      $personal_group_type->installContentPlugin('group_node:taxonomy_test_2');
      $personal_group_type->save();
    }

    $group_admin_role = GroupRole::load('personal-administrator');
    $group_admin_role->grantPermissions(self::PERMISSIONS);
    $group_admin_role->save();

    $this->entityTypeManager = $this->container->get('entity_type.manager');
    $this->configFactory = $this->container->get('config.factory');
    $this->vsiteContextManager = $this->container->get('vsite.context_manager');
    $this->config = $this->configFactory->getEditable('cp_taxonomy.settings');
  }

  /**
   * Set config, show terms on entity pages.
   */
  protected function showTermsOnPage() {
    $this->config->set('display_term_under_content', TRUE);
    $this->config->save(TRUE);
  }

  /**
   * Set config, hide terms on entity pages.
   */
  protected function hideTermsOnPage() {
    $this->config->set('display_term_under_content', FALSE);
    $this->config->save(TRUE);
  }

  /**
   * Set config, show terms on entity listing.
   *
   * @param array $types
   *   Allowed types.
   */
  protected function showTermsOnListing(array $types = []) {
    $this->config->set('display_term_under_content_teaser_types', $types);
    $this->config->save(TRUE);
  }

  /**
   * Set config, hide terms on entity listing.
   */
  protected function hideTermsOnListing() {
    $this->config->set('display_term_under_content_teaser_types', ['node:not_exists_bundle']);
    $this->config->save(TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function tearDown() {
    $group_admin_role = GroupRole::load('personal-administrator');
    $group_admin_role->revokePermissions(self::PERMISSIONS);
    $group_admin_role->save();

    $personal_group_type = GroupType::load('personal');
    if ($personal_group_type->hasContentPlugin('group_node:taxonomy_test_1')) {
      $personal_group_type->uninstallContentPlugin('group_node:taxonomy_test_1');
      $personal_group_type->save();
    }

    if ($personal_group_type->hasContentPlugin('group_node:taxonomy_test_2')) {
      $personal_group_type->uninstallContentPlugin('group_node:taxonomy_test_2');
      $personal_group_type->save();
    }

    parent::tearDown();
  }

}
