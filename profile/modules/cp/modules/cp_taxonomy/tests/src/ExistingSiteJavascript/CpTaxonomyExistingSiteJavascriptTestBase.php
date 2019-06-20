<?php

namespace Drupal\Tests\cp_taxonomy\ExistingSiteJavascript;

use Drupal\group\Entity\GroupInterface;
use Drupal\group\Entity\GroupRole;
use Drupal\group\Entity\GroupType;
use Drupal\media\MediaInterface;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\Entity\Vocabulary;
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
    $this->config = $this->container->get('config.factory');
    $this->vsiteContextManager = $this->container->get('vsite.context_manager');
  }

  /**
   * Creates a taxonomy_test_1.
   *
   * @param array $values
   *   The values used to create the taxonomy_test_1.
   *
   * @return \Drupal\node\NodeInterface
   *   The created node entity.
   */
  protected function createTaxonomyTest1(array $values = []) : NodeInterface {
    $event = $this->createNode($values + [
      'type' => 'taxonomy_test_1',
      'title' => $this->randomString(),
    ]);

    return $event;
  }

  /**
   * Creates a taxonomy_test_2.
   *
   * @param array $values
   *   The values used to create the taxonomy_test_2.
   *
   * @return \Drupal\node\NodeInterface
   *   The created node entity.
   */
  protected function createTaxonomyTest2(array $values = []) : NodeInterface {
    $event = $this->createNode($values + [
      'type' => 'taxonomy_test_2',
      'title' => $this->randomString(),
    ]);

    return $event;
  }

  /**
   * Creates a taxonomy_test_file Media.
   *
   * @param array $values
   *   The values used to create the taxonomy_test_file.
   *
   * @return \Drupal\media\MediaInterface
   *   The created media entity.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function createTaxonomyTestFile(array $values = []) : MediaInterface {
    $media = $this->entityTypeManager->getStorage('media')->create($values + [
      'type' => 'taxonomy_test_file',
      'name' => $this->randomMachineName(),
    ]);
    $media->enforceIsNew();
    $media->save();

    $this->markEntityForCleanup($media);

    return $media;
  }

  /**
   * Create a vocabulary to a group.
   *
   * @param \Drupal\group\Entity\GroupInterface $group
   *   Group entity.
   * @param string $vid
   *   Vocabulary id.
   * @param array $allowed_types
   *   Allowed types for entity bundles.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function createGroupVocabulary(GroupInterface $group, string $vid, array $allowed_types = []) {
    $this->vsiteContextManager->activateVsite($group);
    $vocab = Vocabulary::create([
      'name' => $vid,
      'vid' => $vid,
    ]);
    $vocab->enforceIsNew();
    $vocab->save();
    if (!empty($allowed_types)) {
      $config_vocab = $this->config->getEditable('taxonomy.vocabulary.' . $vid);
      $config_vocab
        ->set('allowed_vocabulary_reference_types', $allowed_types)
        ->save(TRUE);
    }

    $this->markEntityForCleanup($vocab);
  }

  /**
   * Create a vocabulary to a group on cp taxonomy pages.
   *
   * @param \Drupal\group\Entity\GroupInterface $group
   *   Group entity.
   * @param string $vid
   *   Vocabulary id.
   * @param string $name
   *   Taxonomy term name.
   *
   * @return \Drupal\taxonomy\Entity\Term
   *   Created taxonomy term.
   */
  protected function createGroupTerm(GroupInterface $group, string $vid, string $name) {
    $this->vsiteContextManager->activateVsite($group);
    $vocab = Vocabulary::load($vid);
    $term = $this->createTerm($vocab, [
      'name' => $name,
    ]);
    $group->addContent($term, 'group_entity:taxonomy_term');
    return $term;
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
