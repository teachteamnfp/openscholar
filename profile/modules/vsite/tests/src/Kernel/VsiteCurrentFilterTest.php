<?php

namespace Drupal\Tests\vsite\Kernel;

use Drupal\Core\Database\Database;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupType;
use Drupal\group\Entity\GroupTypeInterface;
use Drupal\group\Plugin\GroupContentEnablerManagerInterface;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\Tests\user\Traits\UserCreationTrait;
use Drupal\Tests\views\Kernel\ViewsKernelTestBase;
use Drupal\views\ResultRow;
use Drupal\views\Tests\ViewTestData;
use Drupal\views\Views;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Test the Current Vsite Views Filter.
 *
 * @package Drupal\Tests\vsite\Kernel
 * @group vsite
 * @group kernel
 * @covers \Drupal\vsite\Plugin\views\filter\VsiteCurrentFilter
 */
class VsiteCurrentFilterTest extends ViewsKernelTestBase {

  use UserCreationTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'system',
    'field',
    'node',
    'group',
    'gnode',
    'views',
    'text',
    'filter',
    'purl',
    'vsite',
    'vsite_module_test',
  ];

  /**
   * Views used by this test.
   *
   * @var array
   */
  public static $testViews = ['vsite_test_view'];

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
  protected function setUp($import_test_views = TRUE) {
    \PHPUnit\Framework\Error\Deprecated::$enabled = false;
    parent::setUp(false);
    $this->installSchema('node', 'node_access');
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installEntitySchema('group_type');
    $this->installEntitySchema('group');
    $this->installEntitySchema('group_content_type');
    $this->installEntitySchema('group_content');

    $this->installConfig(['field', 'node', 'group', 'vsite']);

    // Set the current user so group creation can rely on it.
    $this->container->get('current_user')->setAccount($this->createUser());

    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager */
    $entityTypeManager = $this->container->get('entity_type.manager');


    $node_type = NodeType::create(['type' => 'page', 'name' => t('Page')]);
    $node_type->save();

    /** @var GroupTypeInterface $group_type */
    $group_type = $entityTypeManager->getStorage('group_type')->load('personal');

    // Enable the user_as_content plugin on the default group type.
    /** @var \Drupal\group\Entity\Storage\GroupContentTypeStorageInterface $storage */
    $storage = $entityTypeManager->getStorage('group_content_type');
    $plugin = $storage->createFromPlugin($group_type, 'group_node:page');
    $plugin->save();

    ViewTestData::createTestViews(get_class($this), ['vsite_module_test']);

    $this->group = Group::create([
      'type' => 'personal',
      'title' => 'Site01',
    ]);
    $this->group->save();

    $otherGroup = Group::create([
      'type' => 'personal',
      'title' => 'Site02',
    ]);
    $otherGroup->save();

    // Create the nodes we'll be displaying (or not) in the view.
    $this->ungroupedNode = Node::create([
      'type' => 'page',
      'title' => 'Ungrouped'
    ]);
    $this->ungroupedNode->save();

    $this->groupedNode = Node::create([
      'type' => 'page',
      'title' => 'Grouped'
    ]);
    $this->groupedNode->save();

    $otherNode = Node::create([
      'type' => 'page',
      'title' => 'OtherGroup'
    ]);
    $otherNode->save();

    $this->group->addContent($this->groupedNode, $plugin->getContentPluginId());
    $otherGroup->addContent($otherNode, $plugin->getContentPluginId());

    $this->vsiteContextManager = $this->container->get('vsite.context_manager');
  }

  /**
   * Sets up the configuration and schema of views and views_test_data modules.
   *
   * Because the schema of views_test_data.module is dependent on the test
   * using it, it cannot be enabled normally.
   */
  protected function setUpFixtures() {
    // First install the system module. Many Views have Page displays have menu
    // links, and for those to work, the system menus must already be present.
    $this->installConfig(['system']);

    /** @var \Drupal\Core\State\StateInterface $state */
    $state = $this->container->get('state');

    \Drupal::service('plugin.manager.views.filter')->clearCachedDefinitions();

    $this->installConfig(['views', 'vsite_module_test']);

    $this->container->get('router.builder')->rebuild();
  }

  public function register(ContainerBuilder $container) {
    $purlPathProcessor = $this->createMock('\Drupal\purl\PathProcessor\PurlContextOutboundPathProcessor');
    $container->set('purl.outbound_path_processor', $purlPathProcessor);

    parent::register($container);
  }

  /**
   * Retrieves the results for this test's view.
   *
   * @return \Drupal\views\ResultRow[]
   *   A list of view results.
   */
  protected function getViewResults() {
    $view = Views::getView(reset($this::$testViews));
    $view->setDisplay('page_1');

    if ($view->preview()) {
      $names = [];
      foreach ($view->result as $r) {
        $names[] = $r->_entity->label();
      }
      return $names;
    }

    return [];
  }

  /**
   * Check that all posts appear outside a vsite.
   */
   public function testOutsideOfVsite() {
     $results = $this->getViewResults();

     $this->assertContains('Grouped', $results);
     $this->assertContains('Ungrouped', $results);
     $this->assertContains('OtherGroup', $results);
   }

   /**
    * Check that only the grouped post shows up in a vsite.
    */
   public function testInsideOfVsite() {
     $this->vsiteContextManager->activateVsite($this->group);

     $results = $this->getViewResults();

     $this->assertContains('Grouped', $results);
     $this->assertNotContains('Ungrouped', $results, 'View returns Ungrouped when it should not.');
     $this->assertNotContains('OtherGroup', $results, 'View returns OtherGroup when it should not.');
   }

}
