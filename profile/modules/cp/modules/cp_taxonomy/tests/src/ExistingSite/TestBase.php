<?php

namespace Drupal\Tests\cp_taxonomy\ExistingSite;

use Drupal\group\Entity\GroupInterface;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\Tests\vsite\ExistingSite\VsiteExistingSiteTestBase;

/**
 * TestBase for cp_taxonomy tests.
 */
abstract class TestBase extends VsiteExistingSiteTestBase {

  /**
   * Vsite context manager service.
   *
   * @var \Drupal\vsite\Plugin\VsiteContextManagerInterface
   */
  protected $vsiteContextManager;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Taxonomy relation helper service.
   *
   * @var \Drupal\cp_taxonomy\CpTaxonomyHelper
   */
  protected $taxonomyHelper;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->vsiteContextManager = $this->container->get('vsite.context_manager');
    $this->configFactory = $this->container->get('config.factory');
    $this->taxonomyHelper = $this->container->get('cp.taxonomy.helper');
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
      $config_vocab = $this->configFactory->getEditable('taxonomy.vocabulary.' . $vid);
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
   */
  protected function createGroupTerm(GroupInterface $group, string $vid, string $name) {
    $vocab = Vocabulary::load($vid);
    $term = $this->createTerm($vocab, [
      'name' => $name,
    ]);
    $group->addContent($term, 'group_entity:taxonomy_term');
  }

}
