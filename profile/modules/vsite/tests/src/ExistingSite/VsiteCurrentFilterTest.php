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
   * Group parent test entity.
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
  public function setUp() {
    parent::setUp();

    // Set the current user so group creation can rely on it.
    $this->container->get('current_user')->setAccount($this->createUser());

    // Enable the user_as_content plugin on the default group type.
    /** @var \Drupal\group\Entity\Storage\GroupContentTypeStorageInterface $storage */
    $storage = $this->entityTypeManager->getStorage('group_content_type');
    /** @var \Drupal\group\Entity\GroupContentTypeInterface[] $plugin */
    $plugins = $storage->loadByContentPluginId('group_node:page');
    /** @var \Drupal\group\Entity\GroupContentTypeInterface $plugin */
    $plugin = reset($plugins);

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

    $subGroup1 = $this->createGroup([
      'type' => 'subsite_test',
      'label' => 'SubSite01',
      'field_parent_site' => $this->group->id(),
    ]);
    $subGroup2 = $this->createGroup([
      'type' => 'subsite_test',
      'label' => 'SubSite02',
      'field_parent_site' => $otherGroup->id(),
    ]);

    $otherNode = $this->createNode([
      'type' => 'page',
      'title' => 'OtherGroup',
    ]);
    $otherNode->save();
    $subNode1 = $this->createNode([
      'type' => 'page',
      'title' => 'SubNode1',
    ]);
    $subNode1->save();
    $subNode2 = $this->createNode([
      'type' => 'page',
      'title' => 'SubNode2',
    ]);
    $subNode2->save();

    $this->group->addContent($this->groupedNode, $plugin->getContentPluginId());
    $otherGroup->addContent($otherNode, $plugin->getContentPluginId());
    $subGroup1->addContent($subNode1, $plugin->getContentPluginId());
    $subGroup2->addContent($subNode2, $plugin->getContentPluginId());

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

  /**
   * Check that only the subsite grouped post shows up in a vsite.
   */
  public function testSubsiteContentVsite() {
    $this->vsiteContextManager->activateVsite($this->group);

    $results = $this->getViewResults();

    $this->assertContains('SubNode1', $results);
    $this->assertNotContains('SubNode2', $results, 'View returns Other content when it should not.');
  }

}
