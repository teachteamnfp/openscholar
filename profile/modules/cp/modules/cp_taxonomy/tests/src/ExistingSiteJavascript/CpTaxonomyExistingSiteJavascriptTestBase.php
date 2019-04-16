<?php

namespace Drupal\Tests\cp_taxonomy\ExistingSiteJavascript;

use Drupal\group\Entity\GroupInterface;
use Drupal\media\MediaInterface;
use Drupal\node\NodeInterface;
use Drupal\Tests\openscholar\ExistingSiteJavascript\TestBase;

/**
 * Test base for event tests.
 */
abstract class CpTaxonomyExistingSiteJavascriptTestBase extends TestBase {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Test group 1.
   *
   * @var \Drupal\group\Entity\GroupInterface
   */
  protected $group1;

  /**
   * Test group 2.
   *
   * @var \Drupal\group\Entity\GroupInterface
   */
  protected $group2;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  protected $vsiteContextManager;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->entityTypeManager = $this->container->get('entity_type.manager');
    $this->config = $this->container->get('config.factory');
    /** @var \Drupal\vsite\Plugin\VsiteContextManagerInterface $vsite_context_manager */
    $this->vsiteContextManager = $this->container->get('vsite.context_manager');

    $this->group1 = $this->createGroup([
      'path' => [
        'alias' => '/group1',
      ],
    ]);
    $this->group2 = $this->createGroup([
      'path' => [
        'alias' => '/group2',
      ],
    ]);
  }

  /**
   * Creates a group.
   *
   * @param array $values
   *   (optional) The values used to create the entity.
   *
   * @return \Drupal\group\Entity\GroupInterface
   *   The created group entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  protected function createGroup(array $values = []) : GroupInterface {
    $group = $this->entityTypeManager->getStorage('group')->create($values + [
      'type' => 'personal',
      'label' => $this->randomMachineName(),
    ]);
    $group->enforceIsNew();
    $group->save();

    $this->markEntityForCleanup($group);

    return $group;
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
   * Create a vocabulary to a group on cp taxonomy pages.
   *
   * @param \Drupal\group\Entity\GroupInterface $group
   *   Group entity.
   * @param string $vid
   *   Vocabulary id.
   * @param array $allowed_types
   *   Allowed types for entity bundles.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  protected function createGroupVocabulary(GroupInterface $group, string $vid, array $allowed_types = []) {
    $this->vsiteContextManager->activateVsite($group);
    $this->visit($group->get('path')->getValue()[0]['alias'] . '/cp/taxonomy/add');
    $web_assert = $this->assertSession();
    $web_assert->statusCodeEquals(200);
    $page = $this->getCurrentPage();
    $page->fillField('name', $vid);
    $page->fillField('vid', $vid);
    foreach ($allowed_types as $allowed_type) {
      $page->fillField('allowed_entity_types[' . $allowed_type . ']', $allowed_type);
    }
    $submit_button = $page->findButton('Save');
    $submit_button->press();
    file_put_contents('public://createGroupVocabulary' . $group->id() . $vid . $this->getSession()->getStatusCode() . '.png', $this->getSession()->getScreenshot());
    $web_assert->pageTextContains('Created new vocabulary');
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
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  protected function createGroupTerm(GroupInterface $group, string $vid, string $name) {
    $this->vsiteContextManager->activateVsite($group);
    $this->visit($group->get('path')->getValue()[0]['alias'] . '/cp/taxonomy/' . $vid . '/add');
    $web_assert = $this->assertSession();
    $web_assert->statusCodeEquals(200);
    $page = $this->getCurrentPage();
    $page->fillField('name[0][value]', $name);
    $submit_button = $page->findButton('Save');
    $submit_button->press();
    $web_assert->statusCodeEquals(200);
  }

}
