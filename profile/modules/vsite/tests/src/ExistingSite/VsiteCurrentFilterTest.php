<?php

namespace Drupal\Tests\vsite\ExistingSite;

use Drupal\group\Entity\Group;
use Drupal\views\Views;

/**
 * Test the Current Vsite Views Filter.
 *
 * @package Drupal\Tests\vsite\Kernel
 * @group vsite
 * @group kernel
 * @coversDefaultClass \Drupal\vsite\Plugin\views\filter\VsiteCurrentFilter
 */
class VsiteCurrentFilterTest extends VsiteExistingSiteTestBase {

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
  public function setUp($import_test_views = TRUE) {
    parent::setUp();

    // Set the current user so group creation can rely on it.
    $this->container->get('current_user')->setAccount($this->createUser());

    /** @var \Drupal\group\Entity\GroupTypeInterface $group_type */
    $group_type = $this->entityTypeManager->getStorage('group_type')->load('personal');

    // Enable the user_as_content plugin on the default group type.
    /** @var \Drupal\group\Entity\Storage\GroupContentTypeStorageInterface $storage */
    $storage = $this->entityTypeManager->getStorage('group_content_type');
    $plugin = $storage->createFromPlugin($group_type, 'group_node:page');
    $plugin->save();

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
    $this->ungroupedNode = $this->createNode([
      'type' => 'page',
      'title' => 'Ungrouped',
    ]);
    $this->ungroupedNode->save();

    $this->groupedNode = $this->createNode([
      'type' => 'page',
      'title' => 'Grouped',
    ]);
    $this->groupedNode->save();

    $otherNode = $this->createNode([
      'type' => 'page',
      'title' => 'OtherGroup',
    ]);
    $otherNode->save();

    $this->group->addContent($this->groupedNode, $plugin->getContentPluginId());
    $otherGroup->addContent($otherNode, $plugin->getContentPluginId());

    $this->vsiteContextManager = $this->container->get('vsite.context_manager');
  }

  /**
   * Retrieves the results for this test's view.
   *
   * @return \Drupal\views\ResultRow[]
   *   A list of view results.
   */
  protected function getViewResults() {
    $view = Views::getView('vsite_test_view');
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
