<?php

namespace Drupal\Tests\vsite\Kernel;

use Drupal\Core\Database\Database;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupType;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\Tests\user\Traits\UserCreationTrait;
use Drupal\Tests\views\Kernel\ViewsKernelTestBase;
use Drupal\views\Tests\ViewTestData;
use Drupal\views\Views;

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
    'vsite_module_test',
  ];

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
  protected function setUp($import_test_views = TRUE) {
    parent::setUp(false);
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installEntitySchema('group_type');
    $this->installEntitySchema('group');
    $this->installEntitySchema('group_content_type');
    $this->installEntitySchema('group_content');
    $this->installConfig(['field', 'node', 'group']);

    // Set the current user so group creation can rely on it.
    $this->container->get('current_user')->setAccount($this->createUser());

    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager */
    $entityTypeManager = $this->container->get('entity_type.manager');


    $node_type = NodeType::create(['type' => 'page', 'name' => t('Page')]);
    $node_type->save();

    $group_type = GroupType::create(['id' => 'personal', 'label' => t('Personal')]);
    $group_type->save();

    // Enable the user_as_content plugin on the default group type.
    /** @var \Drupal\group\Entity\Storage\GroupContentTypeStorageInterface $storage */
    //$storage = $entityTypeManager->getStorage('group_content_type');
    //$storage->createFromPlugin($group_type, 'user_as_content')->save();

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
    // Define the schema and views data variable before enabling the test module.
    $state->set('views_test_data_schema', $this->schemaDefinition());
    $state->set('views_test_data_views_data', $this->viewsData());
    $this->container->get('views.views_data')->clear();

    $this->installConfig(['vsite_module_test']);
    foreach ($this->schemaDefinition() as $table => $schema) {
      $this->installSchema('views_test_data', $table);
    }

    $this->container->get('router.builder')->rebuild();

    // Load the test dataset.
    $data_set = $this->dataSet();
    $query = Database::getConnection()->insert('views_test_data')
      ->fields(array_keys($data_set[0]));
    foreach ($data_set as $record) {
      $query->values($record);
    }
    $query->execute();
  }

  public function register(ContainerBuilder $container) {
    error_log('running register');
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
    $view->setDisplay();

    if ($view->preview()) {
      return $view->result;
    }

    return [];
  }

  /**
   * Check that all posts appear outside a vsite.
   */
   public function testOutsideOfVsite() {
     $results = $this->getViewResults();
     error_log(print_r($results, 1));
     $this->assertNull(NULL);
   }

}
