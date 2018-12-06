<?php

namespace Drupal\Test\vsite\Kernel;

use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupType;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\Tests\views\Kernel\ViewsKernelTestBase;
use Drupal\views\Tests\ViewTestData;
use Drupal\views\Views;

/**
 * Test the Current Vsite Views Filter.
 *
 * @package Drupal\Test\vsite\Kernel
 * @group vsite
 * @covers \Drupal\vsite\Plugin\views\filter\VsiteCurrentFilter
 */
class VsiteCurrentFilterTest extends ViewsKernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['system', 'views', 'group', 'gnode', 'vsite', 'vsite_module_test'];

  /**
   * Views used by this test.
   *
   * @var array
   */
  public static $testViews = ['vsite_views_test'];

  /**
   * Group dummy content is being assigned (or not) to.
   *
   * @var \Drupal\group\Entity\Group
   */
  protected $group;

  /**
   * An ungrouped node.
   *
   * @var \Drupal\node\Entity\Node
   */
  protected $ungroupedNode;


  /**
   * A grouped node.
   *
   * @var \Drupal\node\Entity\Node
   */
  protected $groupedNode;

  /**
   * Vsite Context Manager.
   *
   * @var \Drupal\vsite\Plugin\VsiteContextManagerInterface
   */
  protected $vsiteContextManager;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installEntitySchema('user');
    $this->installEntitySchema('group');
    $this->installEntitySchema('group_type');
    $this->installEntitySchema('group_content');
    $this->installEntitySchema('group_content_type');

    // Set the current user so group creation can rely on it.
    $this->container->get('current_user')->setAccount($this->createUser());

    // Enable the user_as_content plugin on the default group type.
    /** @var \Drupal\group\Entity\Storage\GroupContentTypeStorageInterface $storage */
    $group_type = $this->entityTypeManager->getStorage('group_type')->load('default');
    $storage = $this->entityTypeManager->getStorage('group_content_type');
    $storage->createFromPlugin($group_type, 'user_as_content')->save();


    $node_type = NodeType::create(['type' => 'page', 'name' => t('Page')]);
    $node_type->save();

    $group_type = GroupType::create(['type' => 'personal', 'name' => t('Personal')]);
    $group_type->save();

    $group_type->installContentPlugin('personal-group_node-page');
    ViewTestData::createTestViews(get_class($this), ['vsite_module_test']);

    $this->group = Group::create([
      'type' => 'personal',
      'title' => 'Site01',
    ]);

    // Create the nodes we'll be displaying (or not) in the view.
    $this->ungroupedNode = Node::create([
      'type' => 'page',
      'title' => 'Ungrouped'
    ]);

    $this->groupedNode = Node::create([
      'type' => 'page',
      'title' => 'Grouped'
    ]);

    $this->group->addContent($this->groupedNode, 'personal-group_node-page');

    $this->vsiteContextManager = $this->container->get('vsite.context_manager');
  }

  /**
   * Retrieves the results for this test's view.
   *
   * @return \Drupal\views\ResultRow[]
   *   A list of view results.
   */
  protected function getViewResults() {
    $view = Views::getView(reset($this::$testViews));
    $view->setDisplay();

    if ($view->preview()) {
      return $view->result;
    }

    return [];
  }

  /**
   * Check that all posts appear outside a vsite.
   */
  protected function testOutsideOfVsite() {
    $results = $this->getViewResults();
    error_log(print_r($results, 1));
    $this->assertNull(null, null);
  }
}