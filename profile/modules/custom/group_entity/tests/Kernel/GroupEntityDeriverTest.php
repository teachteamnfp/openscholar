<?php

namespace Drupal\Tests\group_entity\Kernel;

use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupType;
use Drupal\KernelTests\KernelTestBase;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\Entity\Vocabulary;

/**
 * Class GroupEntityDeriverTest.
 *
 * @package Drupal\Tests\group_entity\Kernel
 * @group kernel
 * @covers GroupEntityDeriver
 */
class GroupEntityDeriverTest extends KernelTestBase {

  /**
   * Modules to install.
   *
   * @var array
   */
  protected static $modules = [
    'system',
    'user',
    'field',
    'text',
    'filter',
    'group',
    'media',
    'block_content',
    'taxonomy',
    'group_entity',
    'group_test_config',
  ];

  /**
   * Group content enabler manager.
   *
   * @var \Drupal\group\Plugin\GroupContentEnablerManagerInterface
   */
  protected $groupContentEnabler;

  /**
   * Group entity to install plugins to.
   *
   * @var \Drupal\group\Entity\GroupType
   */
  protected $groupType;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->installConfig(['group', 'group_test_config', 'field']);
    $this->installEntitySchema('taxonomy_term');
    $this->installEntitySchema('user');
    $this->installEntitySchema('group');
    $this->installEntitySchema('group_type');
    $this->installEntitySchema('group_content');
    $this->installEntitySchema('group_content_type');

    $this->groupContentEnabler = \Drupal::service('plugin.manager.group_content_enabler');

    $this->groupType = GroupType::create([
      'id' => 'personal',
      'label' => 'Personal',
      'creator_membership' => 0,
    ]);
    $this->groupType->save();
  }

  /**
   * Test that our plugins exist.
   */
  public function testPluginsExist() {
    $coll = $this->groupContentEnabler->getAll()->getInstanceIds();
    $this->assertArrayHasKey('group_entity:block_content', $coll);
    $this->assertArrayHasKey('group_entity:media', $coll);
    $this->assertArrayHasKey('group_entity:taxonomy_term', $coll);
  }

  /**
   * Test that plugins are installed.
   */
  public function testCreatePlugin() {
    $this->groupContentEnabler->installEnforced($this->groupType);
    $installed = $this->groupContentEnabler->getInstalledIds($this->groupType);
    $this->assertContains('group_entity:media', $installed);
    $this->assertContains('group_entity:block_content', $installed);
    $this->assertContains('group_entity:taxonomy_term', $installed);
  }

  /**
   * Test grouping content with our plugins.
   */
  public function testGroupingEntity() {
    $this->groupContentEnabler->installEnforced($this->groupType);

    $group = Group::create([
      'type' => 'personal',
      'label' => 'Site01',
    ]);
    $group->save();

    $vocab = Vocabulary::create([
      'vid' => 'test_vocab',
      'label' => 'test vocab',
    ]);
    $vocab->save();

    $term = Term::create([
      'name' => 'test_term',
      'vid' => $vocab->id(),
    ]);
    $term->save();

    $group->addContent($term, 'group_entity:taxonomy_term');
    /** @var \Drupal\taxonomy\Entity\Term[] $saved_terms */
    $saved_terms = $group->getContentEntities('group_entity:taxonomy_term');
    $this->assertEquals($term->id(), $saved_terms[0]->id());
  }

}
